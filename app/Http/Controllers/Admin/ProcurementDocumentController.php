<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\Invoice;
use App\Models\Notification;
use App\Models\ProcurementDocument;
use App\Models\Project;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProcurementDocumentController extends Controller
{
    public function index(Request $request)
    {
        $documents = ProcurementDocument::with(['project.client', 'invoice'])
            ->when($request->filled('project_id'), fn ($query) =>
                $query->where('project_id', $request->integer('project_id')))
            ->when($request->filled('type'), fn ($query) =>
                $query->where('type', (string) $request->string('type')))
            ->when($request->filled('status'), fn ($query) =>
                $query->where('status', (string) $request->string('status')))
            ->latest('document_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $projects = Project::with('client')->latest()->get();

        $summary = [
            'total' => ProcurementDocument::count(),
            'awaiting' => ProcurementDocument::where('status', 'awaiting_signature')->count(),
            'signed' => ProcurementDocument::whereIn('status', ['signed', 'paid', 'archived'])->count(),
            'projects' => ProcurementDocument::distinct('project_id')->count('project_id'),
        ];

        return view('admin.procurement-documents.index', compact(
            'documents',
            'projects',
            'summary',
        ));
    }

    public function create(Request $request)
    {
        $projects = Project::with(['client', 'invoices'])->latest()->get();
        $selectedProject = $request->filled('project_id')
            ? $projects->firstWhere('id', $request->integer('project_id'))
            : null;

        return view('admin.procurement-documents.create', [
            'document' => new ProcurementDocument([
                'project_id' => $selectedProject?->id,
                'document_date' => now()->toDateString(),
                'amount' => $selectedProject?->budget ?? 0,
                'provider_signer_name' => CompanySetting::first()?->director_name,
                'provider_signer_title' => CompanySetting::first()?->director_title,
                'status' => 'draft',
                'transaction_method' => 'non_siplah',
            ]),
            'projects' => $projects,
            'selectedProject' => $selectedProject,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $this->ensureInvoiceBelongsToProject($data);

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
        $procurementDocument->load(['project.client', 'invoice', 'creator']);

        return view('admin.procurement-documents.show', [
            'document' => $procurementDocument,
        ]);
    }

    public function edit(ProcurementDocument $procurementDocument)
    {
        return view('admin.procurement-documents.edit', [
            'document' => $procurementDocument,
            'projects' => Project::with(['client', 'invoices'])->latest()->get(),
            'selectedProject' => $procurementDocument->project,
        ]);
    }

    public function update(Request $request, ProcurementDocument $procurementDocument)
    {
        $previousStatus = $procurementDocument->status;
        $data = $this->validatedData($request, $procurementDocument);
        $this->ensureInvoiceBelongsToProject($data);

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
        $procurementDocument->load(['project.client', 'invoice']);
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
        $project->load(['client', 'invoices.items', 'procurementDocuments' => fn ($query) =>
            $query->orderBy('document_date')->orderBy('id')]);

        return Pdf::loadView('admin.procurement-documents.package-pdf', [
            'project' => $project,
            'company' => CompanySetting::first(),
        ])->setPaper('a4')->stream('paket-spj-'.Str::slug($project->title).'.pdf');
    }

    public function generateProjectPackage(Request $request, Project $project)
    {
        $company = CompanySetting::first();
        $invoice = $project->invoices()->latest()->first();
        $created = 0;

        DB::transaction(function () use ($request, $project, $company, $invoice, &$created) {
            foreach (array_keys(ProcurementDocument::TYPES) as $type) {
                if ($project->procurementDocuments()->where('type', $type)->exists()) {
                    continue;
                }

                $document = ProcurementDocument::create([
                    'project_id' => $project->id,
                    'invoice_id' => in_array($type, ['payment_request', 'receipt'], true)
                        ? $invoice?->id
                        : null,
                    'created_by' => $request->user()->id,
                    'type' => $type,
                    'document_number' => 'TEMP-'.Str::uuid(),
                    'document_date' => now()->toDateString(),
                    'status' => 'draft',
                    'subject' => $project->title,
                    'amount' => $invoice?->total_due ?? $project->budget,
                    'provider_signer_name' => $company?->director_name,
                    'provider_signer_title' => $company?->director_title,
                    'transaction_method' => 'non_siplah',
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
                    ? $created.' draf dokumen pendukung SPJ berhasil dibuat.'
                    : 'Semua jenis dokumen untuk project ini sudah tersedia.',
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
        if ($procurementDocument->signed_file_path) {
            Storage::disk('public')->delete($procurementDocument->signed_file_path);
        }

        $procurementDocument->delete();

        return redirect()
            ->route('procurement-documents.index')
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

    private function notifyClientWhenReady(ProcurementDocument $document): void
    {
        if (!in_array($document->status, ['awaiting_signature', 'signed', 'paid', 'archived'], true)) {
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
