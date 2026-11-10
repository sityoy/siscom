<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\Invoice;
use App\Models\Notification;
use App\Models\ProcurementDocument;
use App\Models\ProcurementPackage;
use App\Models\ProcurementPackageFile;
use App\Models\Project;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Services\ProjectInvoiceSyncService;

class ProcurementDocumentController extends Controller
{
    public function index(Request $request)
    {
        $activeType = $request->route('type') ?: $request->input('type');

        if ($activeType && !array_key_exists($activeType, ProcurementDocument::TYPES)) {
            abort(404);
        }

        $documents = ProcurementDocument::with(['project.client', 'invoice'])
            ->when($request->filled('project_id'), fn ($query) =>
                $query->where('project_id', $request->integer('project_id')))
            ->when($activeType, fn ($query) =>
                $query->where('type', $activeType))
            ->when($request->filled('status'), fn ($query) =>
                $query->where('status', (string) $request->string('status')))
            ->latest('document_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $projects = Project::with('client')
            ->withCount('procurementDocuments')
            ->latest()
            ->get();
        $selectedProject = $request->filled('project_id')
            ? $projects->firstWhere('id', $request->integer('project_id'))
            : null;

        $summaryQuery = ProcurementDocument::query()
            ->when($activeType, fn ($query) => $query->where('type', $activeType));

        $summary = [
            'total' => (clone $summaryQuery)->count(),
            'ready' => (clone $summaryQuery)->whereIn('status', ['ready', 'awaiting_signature'])->count(),
            'signed' => (clone $summaryQuery)->whereIn('status', ['signed', 'paid', 'archived'])->count(),
            'projects' => (clone $summaryQuery)->distinct('project_id')->count('project_id'),
        ];

        return view('admin.procurement-documents.index', compact(
            'documents',
            'projects',
            'selectedProject',
            'summary',
            'activeType',
        ));
    }

    public function packages(Request $request)
    {
        $mode = $request->input('mode');

        if ($mode && !array_key_exists($mode, ProcurementPackage::TYPES)) {
            abort(404);
        }

        $projects = Project::with([
            'client',
            'procurementPackage.files.uploader',
            'invoices' => fn ($query) => $query
                ->where('status', '!=', 'cancelled')
                ->orderByDesc('invoice_date')
                ->orderByDesc('id'),
            'procurementDocuments' => fn ($query) => $query
                ->orderBy('document_date')
                ->orderBy('id'),
        ])
            ->whereHas('invoices', fn ($query) =>
                $query->where('status', '!=', 'cancelled'))
            ->when($mode === 'direct', fn ($query) =>
                $query->where(function ($query) {
                    $query->whereDoesntHave('procurementPackage')
                        ->orWhereHas('procurementPackage', fn ($package) =>
                            $package->where('package_type', 'direct'));
                }))
            ->when($mode === 'third_party', fn ($query) =>
                $query->whereHas('procurementPackage', fn ($package) =>
                    $package->where('package_type', 'third_party')))
            ->latest()
            ->paginate(10);

        return view('admin.procurement-documents.packages', [
            'projects' => $projects,
            'requiredDocumentCount' => count(ProcurementDocument::TYPES),
            'mode' => $mode,
        ]);
    }

    public function editPackage(Project $project)
    {
        $project->load([
            'client',
            'procurementPackage.files.uploader',
            'invoices' => fn ($query) => $query
                ->where('status', '!=', 'cancelled')
                ->orderByDesc('invoice_date')
                ->orderByDesc('id'),
        ]);
        $invoice = $project->invoices->first();

        abort_unless($invoice, 404, 'Project harus memiliki invoice aktif.');

        $company = CompanySetting::first();
        $taxRate = $company?->usesUmkmFinal()
            ? (float) $company->income_tax_rate
            : 0;
        $contractValue = (float) $invoice->grand_total;
        $taxBase = (float) ($invoice->income_tax_base ?: $contractValue);

        $package = $project->procurementPackage ?: new ProcurementPackage([
            'project_id' => $project->id,
            'package_type' => 'direct',
            'procurement_method' => 'non_siplah',
            'status' => 'preparation',
            'contract_value' => $contractValue,
            'target_fee_percent' => 10,
            'income_tax_rate' => $taxRate,
            'income_tax_amount' => $taxBase * $taxRate / 100,
            'tax_collection_method' => $invoice->income_tax_withheld
                ? 'withheld'
                : 'self_paid',
        ]);

        return view('admin.procurement-documents.package-settings', compact(
            'project',
            'invoice',
            'package',
            'company',
        ));
    }

    public function updatePackage(Request $request, Project $project)
    {
        $data = $request->validate([
            'package_type' => ['required', Rule::in(array_keys(ProcurementPackage::TYPES))],
            'procurement_method' => ['required', Rule::in(['siplah', 'non_siplah'])],
            'status' => ['required', Rule::in(array_keys(ProcurementPackage::STATUSES))],
            'vendor_name' => [
                Rule::requiredIf(fn () => $request->input('package_type') === 'third_party'),
                'nullable',
                'string',
                'max:255',
            ],
            'vendor_tax_id' => 'nullable|string|max:50',
            'vendor_tax_status' => ['nullable', Rule::in(['unknown', 'non_pkp', 'pkp', 'pp55', 'individual'])],
            'vendor_contact' => 'nullable|string|max:100',
            'vendor_address' => 'nullable|string|max:1000',
            'contract_value' => 'required|numeric|min:0',
            'vendor_cost' => 'nullable|numeric|min:0',
            'vendor_tax_amount' => 'nullable|numeric|min:0',
            'operational_cost' => 'nullable|numeric|min:0',
            'partner_name' => [
                Rule::requiredIf(fn () => (float) $request->input('partner_fee', 0) > 0),
                'nullable',
                'string',
                'max:255',
            ],
            'partner_fee' => 'nullable|numeric|min:0',
            'target_fee_percent' => 'required|numeric|min:0|max:100',
            'tax_collection_method' => ['required', Rule::in(['withheld', 'self_paid'])],
            'notes' => 'nullable|string|max:3000',
        ]);

        $invoice = app(ProjectInvoiceSyncService::class)->primaryInvoice($project);
        abort_unless($invoice, 422, 'Project harus memiliki invoice aktif.');

        $contractValue = (float) $invoice->grand_total;
        $vendorCost = (float) ($data['vendor_cost'] ?? 0);
        $vendorTaxAmount = (float) ($data['vendor_tax_amount'] ?? 0);
        if ($data['package_type'] === 'direct') {
            $vendorCost = 0;
            $vendorTaxAmount = 0;
        }
        $operationalCost = (float) ($data['operational_cost'] ?? 0);
        $partnerFee = (float) ($data['partner_fee'] ?? 0);
        $company = CompanySetting::first();
        $incomeTaxRate = $company?->usesUmkmFinal()
            ? (float) $company->income_tax_rate
            : 0;
        $taxBase = (float) ($invoice->income_tax_base ?: $contractValue);
        $incomeTaxAmount = $taxBase * $incomeTaxRate / 100;
        $managementFee = $contractValue - $vendorCost;
        $netProfit = $managementFee - $operationalCost - $partnerFee - $incomeTaxAmount;

        if ($netProfit < 0) {
            throw ValidationException::withMessages([
                'contract_value' => 'Pembagian biaya melebihi nilai kontrak. Periksa biaya vendor, operasional, partner, dan pajak.',
            ]);
        }

        if ($vendorTaxAmount > $vendorCost) {
            throw ValidationException::withMessages([
                'vendor_tax_amount' => 'Potongan pajak vendor tidak boleh melebihi biaya vendor.',
            ]);
        }

        $data['vendor_cost'] = $vendorCost;
        $data['vendor_tax_amount'] = $vendorTaxAmount;
        $data['contract_value'] = $contractValue;
        $data['operational_cost'] = $operationalCost;
        $data['partner_fee'] = $partnerFee;
        $data['management_fee_amount'] = $managementFee;
        $data['income_tax_rate'] = $incomeTaxRate;
        $data['income_tax_amount'] = $incomeTaxAmount;
        $data['net_profit'] = $netProfit;

        if ($data['package_type'] === 'direct') {
            $data['vendor_name'] = null;
            $data['vendor_tax_id'] = null;
            $data['vendor_tax_status'] = null;
            $data['vendor_contact'] = null;
            $data['vendor_address'] = null;
        }

        $project->procurementPackage()->updateOrCreate(
            ['project_id' => $project->id],
            $data,
        );

        return redirect()
            ->route('procurement-documents.packages.edit', $project)
            ->with('success', 'Jenis paket SPJ dan pembagian biaya berhasil disimpan.');
    }

    public function uploadPackageFile(Request $request, ProcurementPackage $procurementPackage)
    {
        $data = $request->validate([
            'category' => ['required', Rule::in(array_keys(ProcurementPackageFile::CATEGORIES))],
            'title' => 'nullable|string|max:255',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,xlsx,xls,doc,docx|max:10240',
        ]);
        $file = $request->file('file');
        $path = $file->store(
            'procurement-packages/'.$procurementPackage->id,
            'local',
        );

        $procurementPackage->files()->create([
            'uploaded_by' => $request->user()->id,
            'category' => $data['category'],
            'title' => $data['title'] ?: ProcurementPackageFile::CATEGORIES[$data['category']],
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
        ]);

        return back()->with('success', 'Dokumen pendukung vendor berhasil diunggah.');
    }

    public function downloadPackageFile(ProcurementPackageFile $procurementPackageFile)
    {
        abort_unless(Storage::disk('local')->exists($procurementPackageFile->file_path), 404);

        return Storage::disk('local')->download(
            $procurementPackageFile->file_path,
            $procurementPackageFile->original_name,
        );
    }

    public function destroyPackageFile(ProcurementPackageFile $procurementPackageFile)
    {
        Storage::disk('local')->delete($procurementPackageFile->file_path);
        $procurementPackageFile->delete();

        return back()->with('success', 'Dokumen pendukung vendor berhasil dihapus.');
    }

    public function taxDocuments()
    {
        return view('admin.procurement-documents.tax-documents', [
            'company' => CompanySetting::firstOrCreate([], [
                'company_name' => 'SIS.COM',
                'tax_status' => 'non_pkp',
                'income_tax_scheme' => 'umkm_final',
                'income_tax_rate' => 0.50,
                'pp55_active' => true,
            ]),
        ]);
    }

    public function updateTaxDocuments(Request $request)
    {
        $company = CompanySetting::firstOrCreate([], ['company_name' => 'SIS.COM']);
        $data = $request->validate([
            'tax_status' => ['required', Rule::in(['pkp', 'non_pkp'])],
            'income_tax_scheme' => ['required', Rule::in(['umkm_final', 'normal'])],
            'income_tax_rate' => 'required|numeric|min:0|max:100',
            'pp55_active' => 'nullable|boolean',
            'pp55_case_number' => 'nullable|string|max:100',
            'pp55_bpe_number' => 'nullable|string|max:100',
            'pp55_effective_date' => 'nullable|date',
            'pp55_certificate_number' => 'nullable|string|max:100',
            'pp55_certificate_date' => 'nullable|date',
            'pp55_notes' => 'nullable|string|max:2000',
            'pp55_document' => 'nullable|file|mimes:pdf|max:5120',
            'pp55_certificate_document' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        $data['pp55_active'] = $request->boolean('pp55_active');

        foreach (['pp55_document', 'pp55_certificate_document'] as $field) {
            if (!$request->hasFile($field)) {
                unset($data[$field]);
                continue;
            }

            if ($company->{$field}) {
                Storage::disk('local')->delete($company->{$field});
                Storage::disk('public')->delete($company->{$field});
            }

            $data[$field] = $request->file($field)->store('company/tax');
        }

        $company->update($data);

        return back()->with('success', 'Profil dan dokumen pajak rekanan berhasil diperbarui.');
    }

    public function downloadTaxDocument(string $kind)
    {
        $company = CompanySetting::firstOrFail();
        $field = match ($kind) {
            'bpe' => 'pp55_document',
            'certificate' => 'pp55_certificate_document',
            default => abort(404),
        };
        $path = $company->{$field};

        abort_unless($path, 404);

        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->download($path);
        }

        abort_unless(Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->download($path);
    }

    public function create(Request $request)
    {
        $projects = Project::with([
            'client',
            'invoices' => fn ($query) => $query
                ->where('status', '!=', 'cancelled')
                ->orderByDesc('invoice_date')
                ->orderByDesc('id'),
        ])->latest()->get();
        $selectedProject = $request->filled('project_id')
            ? $projects->firstWhere('id', $request->integer('project_id'))
            : null;
        $selectedInvoice = $selectedProject?->invoices?->first();
        $company = CompanySetting::first();

        return view('admin.procurement-documents.create', [
            'document' => new ProcurementDocument([
                'project_id' => $selectedProject?->id,
                'invoice_id' => $selectedInvoice?->id,
                'document_date' => ($selectedInvoice?->invoice_date
                    ?? $selectedInvoice?->created_at
                    ?? now())->toDateString(),
                'amount' => $selectedInvoice?->total_due
                    ?? $selectedProject?->budget
                    ?? 0,
                'school_signer_name' => $selectedProject?->client?->authorized_official_name,
                'school_signer_title' => $selectedProject?->client?->authorized_official_title,
                'provider_signer_name' => $company?->director_name,
                'provider_signer_title' => $company?->director_title,
                'status' => 'ready',
                'type' => array_key_exists((string) $request->query('type'), ProcurementDocument::TYPES)
                    ? (string) $request->query('type')
                    : 'quotation',
                'transaction_method' => 'non_siplah',
                'provider_tax_status' => $selectedInvoice?->income_tax_scheme === 'umkm_final'
                    ? 'umkm_final'
                    : ($selectedInvoice?->provider_tax_status ?? $company?->tax_status),
                'tax_base' => $selectedInvoice?->income_tax_base ?? 0,
                'vat_amount' => $selectedInvoice?->vat ?? 0,
                'income_tax_amount' => $selectedInvoice?->income_tax_amount ?? 0,
            ]),
            'projects' => $projects,
            'selectedProject' => $selectedProject,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $this->ensureInvoiceBelongsToProject($data);
        $data = $this->applyInvoiceData($data);
        $data = $this->applyDefaultSigners($data);

        $data['created_by'] = $request->user()->id;
        $data['document_number'] = $data['document_number']
            ?: 'TEMP-'.Str::uuid();

        $document = ProcurementDocument::create($data);
        $document->assignAutomaticNumber();
        $this->notifyClientWhenReady($document);

        return redirect()
            ->route('procurement-documents.show', $document)
            ->with('success', 'Dokumen rekanan berhasil dibuat.');
    }

    public function show(ProcurementDocument $procurementDocument)
    {
        $procurementDocument->load(['project.client', 'project.procurementPackage', 'invoice', 'creator']);

        return view('admin.procurement-documents.show', [
            'document' => $procurementDocument,
        ]);
    }

    public function edit(ProcurementDocument $procurementDocument)
    {
        return view('admin.procurement-documents.edit', [
            'document' => $procurementDocument,
            'projects' => Project::with([
                'client',
                'invoices' => fn ($query) => $query
                    ->where('status', '!=', 'cancelled')
                    ->orderByDesc('invoice_date')
                    ->orderByDesc('id'),
            ])->latest()->get(),
            'selectedProject' => $procurementDocument->project,
        ]);
    }

    public function update(Request $request, ProcurementDocument $procurementDocument)
    {
        $previousStatus = $procurementDocument->status;
        $data = $this->validatedData($request, $procurementDocument);
        $this->ensureInvoiceBelongsToProject($data);
        $data = $this->applyInvoiceData($data);
        $data = $this->applyDefaultSigners($data);

        if (empty($data['document_number'])) {
            unset($data['document_number']);
        }

        $procurementDocument->update($data);

        if ($previousStatus !== $procurementDocument->status) {
            $this->notifyClientWhenReady($procurementDocument);
        }

        return redirect()
            ->route('procurement-documents.show', $procurementDocument)
            ->with('success', 'Dokumen rekanan berhasil diperbarui.');
    }

    public function pdf(ProcurementDocument $procurementDocument)
    {
        $procurementDocument->load(['project.client', 'project.procurementPackage', 'invoice']);
        $company = CompanySetting::first();

        return Pdf::loadView('admin.procurement-documents.pdf', [
            'document' => $procurementDocument,
            'company' => $company,
        ])->setPaper('a4')->stream(
            Str::slug($procurementDocument->type_label.'-'.$procurementDocument->document_number).'.pdf'
        );
    }

    public function projectPackage(Project $project)
    {
        $project->load([
            'client',
            'procurementPackage',
            'invoices' => fn ($query) => $query
                ->with('items')
                ->where('status', '!=', 'cancelled')
                ->orderByDesc('invoice_date')
                ->orderByDesc('id'),
            'procurementDocuments' => fn ($query) =>
                $query->orderBy('document_date')->orderBy('id'),
        ]);

        $packageInvoice = $project->invoices->first();

        if (!$packageInvoice) {
            return redirect()
                ->route('procurement-documents.index', ['project_id' => $project->id])
                ->with('error', 'Buat invoice project terlebih dahulu sebelum mencetak Paket SPJ.');
        }

        $packageDate = $packageInvoice?->invoice_date
            ?? $packageInvoice?->created_at
            ?? now();

        $project->procurementDocuments->each(
            fn (ProcurementDocument $document) =>
                $document->setAttribute('document_date', $packageDate),
        );

        return Pdf::loadView('admin.procurement-documents.package-pdf', [
            'project' => $project,
            'company' => CompanySetting::first(),
            'packageInvoice' => $packageInvoice,
            'packageDate' => $packageDate,
        ])->setPaper('a4')->stream('paket-spj-'.Str::slug($project->title).'.pdf');
    }

    public function generateProjectPackage(Request $request, Project $project)
    {
        $company = CompanySetting::first();
        $project->loadMissing('client');
        $invoice = app(ProjectInvoiceSyncService::class)
            ->primaryInvoice($project);

        if (!$invoice) {
            return redirect()
                ->route('projects.index')
                ->with('error', 'Buat invoice project terlebih dahulu sebelum membuat Paket SPJ.');
        }

        $project->procurementPackage()->firstOrCreate(
            ['project_id' => $project->id],
            [
                'package_type' => 'direct',
                'procurement_method' => 'non_siplah',
                'status' => 'documents',
                'contract_value' => $invoice->grand_total,
                'target_fee_percent' => 10,
                'income_tax_rate' => $company?->usesUmkmFinal()
                    ? $company->income_tax_rate
                    : 0,
                'income_tax_amount' => $company?->usesUmkmFinal()
                    ? (float) $invoice->income_tax_base * (float) $company->income_tax_rate / 100
                    : 0,
                'tax_collection_method' => $invoice->income_tax_withheld
                    ? 'withheld'
                    : 'self_paid',
                'management_fee_amount' => $invoice->grand_total,
                'net_profit' => (float) $invoice->grand_total
                    - ($company?->usesUmkmFinal()
                        ? (float) $invoice->income_tax_base * (float) $company->income_tax_rate / 100
                        : 0),
            ],
        );

        $documentDate = $invoice?->invoice_date
            ?? $invoice?->created_at
            ?? now();
        $created = 0;

        DB::transaction(function () use ($request, $project, $company, $invoice, $documentDate, &$created) {
            foreach (array_keys(ProcurementDocument::TYPES) as $type) {
                if ($project->procurementDocuments()->where('type', $type)->exists()) {
                    continue;
                }

                $document = ProcurementDocument::create([
                    'project_id' => $project->id,
                    'invoice_id' => $invoice?->id,
                    'created_by' => $request->user()->id,
                    'type' => $type,
                    'document_number' => 'TEMP-'.Str::uuid(),
                    'document_date' => $documentDate->toDateString(),
                    'status' => 'ready',
                    'subject' => $project->title,
                    'amount' => $invoice?->total_due ?? $project->budget,
                    'school_signer_name' => $project->client?->authorized_official_name,
                    'school_signer_title' => $project->client?->authorized_official_title,
                    'provider_signer_name' => $company?->director_name,
                    'provider_signer_title' => $company?->director_title,
                    'transaction_method' => 'non_siplah',
                    'provider_tax_status' => $invoice->income_tax_scheme === 'umkm_final'
                        ? 'umkm_final'
                        : $invoice->provider_tax_status,
                    'tax_base' => $invoice->income_tax_base,
                    'vat_amount' => $invoice->vat,
                    'income_tax_amount' => $invoice->income_tax_amount,
                    'terms' => $this->defaultTerms($type),
                ]);

                $document->assignAutomaticNumber();
                $created++;
            }
        });

        return redirect()
            ->route('procurement-documents.index', ['project_id' => $project->id])
            ->with(
                'success',
                $created > 0
                    ? $created.' dokumen pendukung SPJ berhasil dibuat.'
                    : 'Semua jenis dokumen untuk project ini sudah tersedia.',
            );
    }

    public function destroyProjectPackage(Project $project)
    {
        $documents = $project->procurementDocuments()->get();

        if ($documents->isEmpty()) {
            return back()->with('success', 'Paket SPJ project ini sudah kosong.');
        }

        $signedFilePaths = $documents
            ->pluck('signed_file_path')
            ->filter()
            ->values()
            ->all();
        $deletedCount = $documents->count();

        DB::transaction(function () use ($project) {
            $project->procurementDocuments()->delete();
        });

        if ($signedFilePaths !== []) {
            Storage::disk('public')->delete($signedFilePaths);
        }

        return back()->with(
            'success',
            $deletedCount.' dokumen dalam paket SPJ berhasil dihapus.',
        );
    }

    public function uploadSigned(Request $request, ProcurementDocument $procurementDocument)
    {
        $request->validate([
            'signed_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($procurementDocument->signed_file_path) {
            Storage::disk('public')->delete($procurementDocument->signed_file_path);
        }

        $path = $request->file('signed_file')->store('procurement-documents/signed', 'public');

        $procurementDocument->update([
            'signed_file_path' => $path,
            'status' => $procurementDocument->status === 'paid' ? 'paid' : 'signed',
        ]);

        $this->notifyClientWhenReady($procurementDocument);

        return back()->with('success', 'Dokumen bertanda tangan berhasil diunggah.');
    }

    public function downloadSigned(ProcurementDocument $procurementDocument)
    {
        abort_unless(
            $procurementDocument->signed_file_path
                && Storage::disk('public')->exists($procurementDocument->signed_file_path),
            404,
        );

        return Storage::disk('public')->download(
            $procurementDocument->signed_file_path,
            'signed-'.basename($procurementDocument->signed_file_path),
        );
    }

    public function destroy(ProcurementDocument $procurementDocument)
    {
        $projectId = $procurementDocument->project_id;

        if ($procurementDocument->signed_file_path) {
            Storage::disk('public')->delete($procurementDocument->signed_file_path);
        }

        $procurementDocument->delete();

        return redirect()
            ->route('procurement-documents.index', ['project_id' => $projectId])
            ->with('success', 'Dokumen rekanan berhasil dihapus.');
    }

    private function validatedData(
        Request $request,
        ?ProcurementDocument $document = null,
    ): array {
        return $request->validate([
            'project_id' => 'required|exists:projects,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'type' => ['required', Rule::in(array_keys(ProcurementDocument::TYPES))],
            'document_number' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('procurement_documents', 'document_number')->ignore($document),
            ],
            'document_date' => 'required|date',
            'status' => ['required', Rule::in(array_keys(ProcurementDocument::STATUSES))],
            'title' => 'nullable|string|max:255',
            'subject' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'work_start_date' => 'nullable|date',
            'work_end_date' => 'nullable|date|after_or_equal:work_start_date',
            'school_signer_name' => 'nullable|string|max:255',
            'school_signer_title' => 'nullable|string|max:255',
            'provider_signer_name' => 'nullable|string|max:255',
            'provider_signer_title' => 'nullable|string|max:255',
            'funding_source' => 'nullable|string|max:50',
            'transaction_method' => ['nullable', Rule::in(['siplah', 'non_siplah'])],
            'transaction_type' => ['nullable', Rule::in(['goods', 'services', 'rental', 'other'])],
            'provider_tax_status' => ['nullable', Rule::in(['pkp', 'non_pkp', 'umkm_final', 'other'])],
            'tax_base' => 'nullable|numeric|min:0',
            'vat_amount' => 'nullable|numeric|min:0',
            'income_tax_amount' => 'nullable|numeric|min:0',
            'terms' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
    }

    private function ensureInvoiceBelongsToProject(array $data): void
    {
        if (empty($data['invoice_id'])) {
            return;
        }

        $valid = Invoice::whereKey($data['invoice_id'])
            ->where('project_id', $data['project_id'])
            ->exists();

        if (!$valid) {
            throw ValidationException::withMessages([
                'invoice_id' => 'Invoice tidak terhubung dengan project yang dipilih.',
            ]);
        }
    }

    private function applyDefaultSigners(array $data): array
    {
        $project = Project::with('client')->findOrFail($data['project_id']);
        $company = CompanySetting::first();

        $data['school_signer_name'] = ($data['school_signer_name'] ?? null)
            ?: $project->client?->authorized_official_name;
        $data['school_signer_title'] = ($data['school_signer_title'] ?? null)
            ?: $project->client?->authorized_official_title;
        $data['provider_signer_name'] = ($data['provider_signer_name'] ?? null)
            ?: $company?->director_name;
        $data['provider_signer_title'] = ($data['provider_signer_title'] ?? null)
            ?: $company?->director_title;

        return $data;
    }

    private function applyInvoiceData(array $data): array
    {
        if (empty($data['invoice_id'])) {
            return $data;
        }

        $invoice = Invoice::findOrFail($data['invoice_id']);
        $invoiceDate = $invoice->invoice_date
            ?? $invoice->created_at
            ?? now();

        $data['document_date'] = $invoiceDate->toDateString();
        $data['amount'] = $invoice->total_due;
        $data['provider_tax_status'] = $invoice->income_tax_scheme === 'umkm_final'
            ? 'umkm_final'
            : $invoice->provider_tax_status;
        $data['tax_base'] = $invoice->income_tax_base;
        $data['vat_amount'] = $invoice->vat;
        $data['income_tax_amount'] = $invoice->income_tax_amount;

        return $data;
    }

    private function notifyClientWhenReady(ProcurementDocument $document): void
    {
        if (!in_array($document->status, ['ready', 'awaiting_signature', 'signed', 'paid', 'archived'], true)) {
            return;
        }

        Notification::create([
            'client_id' => $document->project->client_id,
            'title' => 'Dokumen Rekanan: '.$document->type_label,
            'message' => 'Dokumen '.$document->document_number.' untuk project "'
                .$document->project->title.'" tersedia di portal client.',
        ]);
    }

    private function defaultTerms(string $type): ?string
    {
        return match ($type) {
            'quotation' => "Penawaran berlaku selama 30 hari sejak tanggal dokumen.\nPerubahan ruang lingkup dan biaya harus disepakati secara tertulis.",
            'negotiation_minutes' => "Harga akhir dan ruang lingkup mengikuti hasil kesepakatan para pihak.\nHasil negosiasi menjadi dasar penyusunan Surat Pesanan/SPK.",
            'purchase_order' => "Pekerjaan dilaksanakan sesuai ruang lingkup yang disepakati.\nPerubahan pekerjaan harus mendapat persetujuan tertulis para pihak.\nPembayaran dilakukan berdasarkan invoice dan dokumen serah terima.",
            'work_order' => "Pelaksana wajib menjalankan tugas sesuai ruang lingkup dan jadwal.\nHasil pelaksanaan dilaporkan kepada pihak sekolah.",
            'work_report' => "Laporan dilengkapi dokumentasi atau bukti hasil pekerjaan.\nCatatan pekerjaan lanjutan dicantumkan secara tertulis.",
            'handover' => "Pihak sekolah wajib memeriksa kesesuaian hasil pekerjaan sebelum menandatangani BAST.\nCatatan kekurangan atau perbaikan wajib dicantumkan pada dokumen.",
            'payment_request' => "Pembayaran mengikuti nilai invoice dan ketentuan pajak yang berlaku.\nBukti pembayaran disampaikan setelah transaksi diselesaikan.",
            'receipt' => "Kwitansi dinyatakan sah setelah pembayaran diterima dan dokumen ditandatangani.",
            default => null,
        };
    }
}
