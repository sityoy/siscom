<?php

namespace App\Http\Controllers\Admin;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\InvoiceItem;
use App\Mail\InvoiceMail;
use App\Services\InvoiceReminderScheduler;
use App\Services\InvoiceApprovalArtwork;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Notification;
use App\Models\CompanySetting;
use App\Services\ProjectInvoiceSyncService;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    public function index(Request $request)
{
    $query = Invoice::with([
        'client',
        'project',
        'items',
    ])->withExists(['agreement', 'payments'])->withSum('payments as amount_paid', 'amount');

    if ($request->type) {

        $query->where(
            'invoice_type',
            $request->type
        );
    }

    $invoices = $query
        ->latest()
        ->paginate(\App\Support\Pagination::perPage());

    return view(
        'admin.invoices.index',
        compact('invoices')
    );
}

    public function create(Request $request)
    {
        $clients = Client::all();

        $company = $this->companyTaxSetting();

        $projects = Project::withCount('invoices')->get();

        $selectedProject = null;

        if ($request->filled('project_id')) {
            $selectedProject = Project::with('client')
                ->find($request->integer('project_id'));

            $existingInvoice = $selectedProject?->invoices()
                ->latest('invoice_date')
                ->latest('id')
                ->first();

            if ($existingInvoice) {
                return redirect()
                    ->route('invoices.edit', $existingInvoice)
                    ->with('warning', 'Project ini sudah memiliki invoice. Silakan edit invoice yang sudah ada.');
            }
        }

        $requestedType = (string) $request->input('type');
        $invoiceType = array_key_exists($requestedType, Invoice::TYPES)
            ? $requestedType
            : ($selectedProject ? 'project' : 'general');

        $invoiceDefaults = [
            'client_id' => $selectedProject?->client_id,
            'project_id' => $selectedProject?->id,
            'invoice_type' => $invoiceType,
            'invoice_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'description' => $selectedProject
                ? ($invoiceType === 'spj' ? 'Pengadaan - ' : 'Biaya Project - ')
                    . $selectedProject->title
                : '',
            'item_type' => $invoiceType === 'general' ? 'goods' : 'service',
            'specification' => '',
            'unit' => $invoiceType === 'general' ? 'Pcs' : 'Paket',
            'price' => $selectedProject?->budget,
            'duration' => 1,
            'duration_type' => 'Hari',
            'start_date' => null,
            'end_date' => null,
            'vat_percent' => $company->isPkp() ? 11 : 0,
            'service_fee' => in_array($invoiceType, ['general', 'spj'], true)
                ? 0
                : 10000,
            'income_tax_withheld' => false,
            'late_fee_active' => (bool) ($selectedProject?->late_fee_active),
            'late_fee_per_month' => $selectedProject?->late_fee_per_month
                ?? 100000,
            'notes' => $selectedProject
                ? ($invoiceType === 'spj' ? 'Invoice SPJ/Rekanan - ' : 'Invoice Project ')
                    . $selectedProject->title
                : '',
        ];

        return view(
            'admin.invoices.create',
            compact(
                'clients',
                'projects',
                'invoiceDefaults',
                'company'
            )
        );
    }

    public function store(Request $request)
    {
        $this->normalizeLegacyItemDetails($request);

        $request->validate([

            'client_id' => 'required|exists:clients,id',

            'project_id' => 'nullable|required_if:invoice_type,spj|exists:projects,id',

            'invoice_type' => 'required|in:general,project,spj,renewal',

            'client_signature_mode' => ['sometimes', 'required', 'string', Rule::in(array_keys(Invoice::CLIENT_SIGNATURE_MODES))],

            'company_signature_mode' => ['sometimes', 'required', 'string', Rule::in(array_keys(Invoice::COMPANY_SIGNATURE_MODES))],

            'invoice_number' => 'required|string|max:100|unique:invoices,invoice_number',

            'invoice_date' => 'required|date',

            'due_date' => 'required|date|after_or_equal:invoice_date',

            'status' => 'required|in:unpaid,partial,paid,cancelled',

            'continue_to_payment' => 'sometimes|boolean',

            'vat_percent' => 'nullable|numeric|min:0|max:100',

            'cashback' => 'nullable|numeric|min:0|max:20',

            'service_fee' => 'nullable|numeric|min:0',

            'late_fee_active' => 'nullable|boolean',

            'late_fee_per_month' => 'nullable|required_if:late_fee_active,1|numeric|min:0',

            'income_tax_withheld' => 'nullable|boolean',

            'description' => 'required|array|min:1',

            'description.*' => 'required|string|max:255',

            'qty' => 'required|array|min:1',

            'qty.*' => 'required|numeric|min:1',

            'item_type' => 'required|array|min:1',

            'item_type.*' => 'required|in:goods,service,rental,license,other',

            'specification' => 'nullable|array',

            'specification.*' => 'nullable|string|max:2000',

            'unit' => 'required|array|min:1',

            'unit.*' => 'required|string|max:30',

            'price' => 'required|array|min:1',

            'price.*' => 'required|numeric|min:0',
        ]);

        $this->validateItemArrays($request);
        $companySignatureMode = $this->companySignatureMode($request);

        $project = $request->filled('project_id')
            ? Project::findOrFail($request->integer('project_id'))
            : null;

        if ($project?->invoices()->exists()) {
            throw ValidationException::withMessages([
                'project_id' => 'Project ini sudah memiliki invoice. Gunakan menu Edit Invoice.',
            ]);
        }

        $subtotal = 0;

        foreach ($request->price as $index => $price) {

            $qty = $request->qty[$index];

            $subtotal += $qty * $price;
        }

        $serviceFee = $request->service_fee ?? 0;

        $company = $this->companyTaxSetting();

        $tax = $this->taxSnapshot(
            $company,
            $subtotal,
            $serviceFee,
            $request->boolean('income_tax_withheld'),
            (float) ($request->vat_percent ?? 0),
        );

        $vatPercent = $tax['vat_percent'];

        $vat = $tax['vat'];

        $cashback = $request->cashback ?? 0;

        // $cashbackAmount =
        //     ($subtotal + $vat + $serviceFee)
        //     * $cashback / 100;

        $grandTotal =
        $subtotal + $vat + $serviceFee;


        $invoice = new Invoice([

            'client_id' => $project?->client_id ?? $request->client_id,

            'project_id' => $request->project_id,

            'invoice_number' => $request->invoice_number,

            'invoice_date' => $request->invoice_date,

            'invoice_type' => $request->invoice_type,

            'client_signature_mode' => $request->input('client_signature_mode', 'none'),

            'company_signature_mode' => $companySignatureMode,
            'verification_code' => $companySignatureMode === 'blank' ? null : bin2hex(random_bytes(20)),

            'subtotal' => $subtotal,

            'vat_percent' => $vatPercent,

            'vat' => $vat,

            'provider_tax_status' => $tax['provider_tax_status'],

            'income_tax_scheme' => $tax['income_tax_scheme'],

            'income_tax_rate' => $tax['income_tax_rate'],

            'income_tax_base' => $tax['income_tax_base'],

            'income_tax_withheld' => $tax['income_tax_withheld'],

            'income_tax_amount' => $tax['income_tax_amount'],

            'service_fee' => $serviceFee,

            'late_fee_active' => $project
                ? (bool) $project->late_fee_active
                : $request->boolean('late_fee_active'),

            'late_fee_per_month' => $project
                ? ($project->late_fee_active ? $project->late_fee_per_month : 0)
                : ($request->boolean('late_fee_active') ? $request->late_fee_per_month : 0),

            'cashback' => $cashback,

            'grand_total' => $grandTotal,

            'due_date' => $request->due_date,

            'status' => $request->status,

            'paid_at' => $request->status === 'paid'
                ? now()
                : null,

            'notes' => $request->notes,

        ]);

        $continueToPayment = $request->boolean('continue_to_payment');
        $paymentBlock = $invoice->paymentBlockReason(0);
        $hasPaymentAction = $paymentBlock === null || $invoice->canRecordUnloggedSettlement();
        if ($continueToPayment && ! $hasPaymentAction) {
            throw ValidationException::withMessages(['continue_to_payment' => $paymentBlock]);
        }

        $invoice->save();

        Notification::create([

            'client_id' => $invoice->client_id,

            'title' => 'Invoice Baru',


            'message' =>
                'Invoice baru telah dibuat dengan nominal Rp ' .
                number_format(
                    $invoice->grand_total,
                    0,
                    ',',
                    '.'
                ),
        ]);

        foreach ($request->description as $index => $description) {

            $qty = $request->qty[$index];

            $price = $request->price[$index];

            $itemType = $request->item_type[$index];

            $usesPeriod = $request->invoice_type !== 'spj'
                && (
                    in_array($request->invoice_type, ['project', 'renewal'], true)
                    || in_array($itemType, ['service', 'rental'], true)
                );

            $total = $qty * $price;

            InvoiceItem::create([

                'invoice_id' => $invoice->id,

                'description' => $description,

                'item_type' => $itemType,

                'specification' => $request->specification[$index] ?? null,

                'qty' => $qty,

                'unit' => $request->unit[$index],

                'price' => $price,

                'total' => $total,

                'duration' => $usesPeriod ? ($request->duration[$index] ?? null) : null,

                'duration_type' => $usesPeriod ? ($request->duration_type[$index] ?? null) : null,

                'start_date' => $usesPeriod ? ($request->start_date[$index] ?? null) : null,

                'end_date' => $usesPeriod ? ($request->end_date[$index] ?? null) : null,

            ]);
        }

        app(ProjectInvoiceSyncService::class)
            ->syncAfterInvoiceSaved($invoice->fresh('project'));

        // EMAIL
        try {

            Mail::to(
                $invoice->client->email
            )->send(
                new InvoiceMail($invoice)
            );


            } catch (\Exception $e) {

                // skip email error localhost

            }

        // WHATSAPP
        try {

            app(InvoiceReminderScheduler::class)
                ->scheduleInvoice($invoice);

        } catch (\Exception $e) {

            report($e);

        }

        if ($continueToPayment) {
            return to_route('payments.create', ['invoice_id' => $invoice->id])
                ->with('success', 'Invoice berhasil dibuat. Isi rincian pembayaran di bawah.');
        }

        return to_route('invoices.index')
            ->with('success', 'Invoice berhasil dibuat')
            ->with('new_invoice_payment_id', $hasPaymentAction ? $invoice->id : null);
    }

    public function edit(Invoice $invoice)
    {
        $clients = Client::all();

        $company = $this->companyTaxSetting();

        $projects = Project::withCount('invoices')->get();
        $paymentBlock = $invoice->paymentBlockReason($invoice->recordedPaymentsTotal());
        $recordSettlement = $invoice->canRecordUnloggedSettlement();

        return view(
            'admin.invoices.edit',
            compact(
                'invoice',
                'clients',
                'projects',
                'company',
                'paymentBlock',
                'recordSettlement'
            )
        );
    }

    public function update(Request $request, Invoice $invoice)
    {
            $this->normalizeLegacyItemDetails($request);

            $request->validate([
                'client_id' => 'required|exists:clients,id',
                'project_id' => 'nullable|required_if:invoice_type,spj|exists:projects,id',
                'invoice_number' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('invoices', 'invoice_number')->ignore($invoice->id),
                ],
                'invoice_date' => 'required|date',
                'due_date' => 'required|date|after_or_equal:invoice_date',
                'status' => 'required|in:unpaid,partial,paid,cancelled',
                'vat_percent' => 'required|numeric|min:0|max:100',
                'cashback' => 'nullable|numeric|min:0|max:20',
                'invoice_type' => 'required|in:general,project,spj,renewal',
                'client_signature_mode' => ['sometimes', 'required', 'string', Rule::in(array_keys(Invoice::CLIENT_SIGNATURE_MODES))],
                'company_signature_mode' => ['sometimes', 'required', 'string', Rule::in(array_keys(Invoice::COMPANY_SIGNATURE_MODES))],
                'late_fee_active' => 'nullable|boolean',
                'late_fee_per_month' => 'nullable|required_if:late_fee_active,1|numeric|min:0',
                'income_tax_withheld' => 'nullable|boolean',
                'description' => 'required|array|min:1',
                'description.*' => 'required|string|max:255',
                'qty' => 'required|array|min:1',
                'qty.*' => 'required|numeric|min:1',
                'price' => 'required|array|min:1',
                'price.*' => 'required|numeric|min:0',
                'item_type' => 'required|array|min:1',
                'item_type.*' => 'required|in:goods,service,rental,license,other',
                'specification' => 'nullable|array',
                'specification.*' => 'nullable|string|max:2000',
                'unit' => 'required|array|min:1',
                'unit.*' => 'required|string|max:30',
            ]);

            $this->validateItemArrays($request);
            $companySignatureMode = $this->companySignatureMode($request, $invoice);

            $previousProjectId = $invoice->project_id;
            $project = $request->filled('project_id')
                ? Project::findOrFail($request->integer('project_id'))
                : null;

            if (
                $project
                && $project->invoices()->whereKeyNot($invoice->id)->exists()
            ) {
                throw ValidationException::withMessages([
                    'project_id' => 'Project yang dipilih sudah memiliki invoice lain.',
                ]);
            }

            $subtotal = 0;

            foreach ($request->price as $index => $price) {

                $qty = $request->qty[$index];

                $subtotal += $qty * $price;
            }

            $serviceFee = $request->service_fee ?? 0;

            $company = $this->companyTaxSetting();

            $tax = $this->taxSnapshot(
                $company,
                $subtotal,
                $serviceFee,
                $request->boolean('income_tax_withheld'),
                (float) ($request->vat_percent ?? 0),
            );

            $vatPercent = $tax['vat_percent'];

            $vat = $tax['vat'];

            $cashback = $request->cashback ?? 0;

            // $cashbackAmount =
            //     ($subtotal + $vat + $serviceFee)
            //     * $cashback / 100;

            $grandTotal =
                $subtotal + $vat + $serviceFee;


            $invoice->update([

                'client_id' => $project?->client_id ?? $request->client_id,

                'project_id' => $request->project_id,

                'invoice_number' => $request->invoice_number,

                'invoice_date' => $request->invoice_date,

                'invoice_type' => $request->invoice_type,

                'client_signature_mode' => $request->input('client_signature_mode', $invoice->client_signature_mode),

                'company_signature_mode' => $companySignatureMode,
                'verification_code' => $companySignatureMode === 'blank' ? null : bin2hex(random_bytes(20)),

                'subtotal' => $subtotal,

                'vat_percent' => $vatPercent,

                'vat' => $vat,

                'provider_tax_status' => $tax['provider_tax_status'],

                'income_tax_scheme' => $tax['income_tax_scheme'],

                'income_tax_rate' => $tax['income_tax_rate'],

                'income_tax_base' => $tax['income_tax_base'],

                'income_tax_withheld' => $tax['income_tax_withheld'],

                'income_tax_amount' => $tax['income_tax_amount'],

                'service_fee' => $serviceFee,

                'late_fee_active' => $project
                    ? (bool) $project->late_fee_active
                    : $request->boolean('late_fee_active'),

                'late_fee_per_month' => $project
                    ? ($project->late_fee_active ? $project->late_fee_per_month : 0)
                    : ($request->boolean('late_fee_active') ? $request->late_fee_per_month : 0),

                'cashback' => $cashback,

                'grand_total' => $grandTotal,

                'due_date' => $request->due_date,

                'status' => $request->status,

                'paid_at' => $request->status === 'paid'
                    ? ($invoice->paid_at ?? now())
                    : null,

                'notes' => $request->notes,

            ]);

            $invoice->items()->delete();

            foreach ($request->description as $index => $description) {

                $qty = $request->qty[$index];

                $price = $request->price[$index];

                $itemType = $request->item_type[$index];

                $usesPeriod = $request->invoice_type !== 'spj'
                    && (
                        in_array($request->invoice_type, ['project', 'renewal'], true)
                        || in_array($itemType, ['service', 'rental'], true)
                    );

                $total = $qty * $price;


                InvoiceItem::create([

                    'invoice_id' => $invoice->id,

                    'description' => $description,

                    'item_type' => $itemType,

                    'specification' => $request->specification[$index] ?? null,

                    'qty' => $qty,

                    'unit' => $request->unit[$index],

                    'price' => $price,

                    'total' => $total,

                    'duration' => $usesPeriod ? ($request->duration[$index] ?? null) : null,

                    'duration_type' => $usesPeriod ? ($request->duration_type[$index] ?? null) : null,

                    'start_date' => $usesPeriod ? ($request->start_date[$index] ?? null) : null,

                    'end_date' => $usesPeriod ? ($request->end_date[$index] ?? null) : null,

                ]);


            }

            app(ProjectInvoiceSyncService::class)
                ->syncAfterInvoiceSaved($invoice->fresh('project'), $previousProjectId);

            return redirect()
                ->route('invoices.index')
                ->with(
                    'success',
                    'Invoice berhasil diupdate'
                );
    }

    public function destroy(Invoice $invoice)
    {
        $projectId = $invoice->project_id;

        $invoice->items()->delete();

        $invoice->delete();

        if ($projectId && ($project = Project::find($projectId))) {
            app(ProjectInvoiceSyncService::class)
                ->syncProjectPackage($project);
        }

        return back()->with(
            'success',
            'Invoice berhasil dihapus'
        );
    }

    public function pdf(Request $request, Invoice $invoice)
    {
        $invoice->loadMissing(['client.user', 'project', 'items']);
        $company = CompanySetting::first();
        $language = $this->invoiceLanguage($request);

        $pdf = Pdf::loadView(
            'admin.invoices.pdf',
            compact('invoice', 'company', 'language')

        );


        return $pdf->download(
            'invoice-' .
            $invoice->invoice_number . '-' . strtoupper($language) .
            '.pdf'
        );


    }

    public function viewPdf(Request $request, Invoice $invoice)
    {
        $invoice->loadMissing(['client.user', 'project', 'items']);
        $company = CompanySetting::first();
        $language = $this->invoiceLanguage($request);

        $pdf = Pdf::loadView(
            'admin.invoices.pdf',
            compact('invoice', 'company', 'language')
        );

        return $pdf->stream();
    }

    public function whatsappPdf(Request $request, Invoice $invoice)
    {
        return $this->viewPdf($request, $invoice);
    }

    private function invoiceLanguage(Request $request): string
    {
        return in_array($request->query('lang'), ['id', 'en'], true)
            ? $request->query('lang')
            : 'id';
    }

    private function companySignatureMode(Request $request, ?Invoice $invoice = null): string
    {
        $mode = $request->input('company_signature_mode', $invoice?->company_signature_mode ?? 'blank');

        if ($mode === 'artwork' && ! app(InvoiceApprovalArtwork::class)->exists()) {
            throw ValidationException::withMessages([
                'company_signature_mode' => 'Unggah tanda tangan dan stempel PT di Pengaturan Perusahaan terlebih dahulu.',
            ]);
        }

        return $mode;
    }

    private function companyTaxSetting(): CompanySetting
    {
        return CompanySetting::firstOrCreate([], [
            'company_name' => 'SIS.COM',
            'tax_status' => 'non_pkp',
            'income_tax_scheme' => 'umkm_final',
            'income_tax_rate' => 0.50,
            'pp55_active' => true,
        ]);
    }

    private function taxSnapshot(
        CompanySetting $company,
        float $subtotal,
        float $serviceFee,
        bool $withheld,
        float $requestedVatPercent,
    ): array {
        $vatPercent = $company->isPkp() ? $requestedVatPercent : 0;
        $incomeTaxBase = $subtotal + $serviceFee;
        $incomeTaxRate = $company->usesUmkmFinal()
            ? (float) $company->income_tax_rate
            : 0;
        $incomeTaxWithheld = $withheld && $incomeTaxRate > 0;

        return [
            'vat_percent' => $vatPercent,
            'vat' => $subtotal * $vatPercent / 100,
            'provider_tax_status' => $company->tax_status ?: 'non_pkp',
            'income_tax_scheme' => $company->income_tax_scheme ?: 'umkm_final',
            'income_tax_rate' => $incomeTaxRate,
            'income_tax_base' => $incomeTaxBase,
            'income_tax_withheld' => $incomeTaxWithheld,
            'income_tax_amount' => $incomeTaxWithheld
                ? $incomeTaxBase * $incomeTaxRate / 100
                : 0,
        ];
    }

    private function validateItemArrays(Request $request): void
    {
        $expected = count($request->input('description', []));

        foreach (['qty', 'item_type', 'unit', 'price'] as $field) {
            if (count($request->input($field, [])) !== $expected) {
                throw ValidationException::withMessages([
                    $field => 'Rincian item invoice tidak lengkap. Muat ulang halaman lalu periksa kembali setiap baris.',
                ]);
            }
        }
    }

    private function normalizeLegacyItemDetails(Request $request): void
    {
        $itemCount = count($request->input('description', []));

        if ($itemCount === 0) {
            return;
        }

        $defaults = [];

        if (!$request->has('item_type')) {
            $defaults['item_type'] = array_fill(0, $itemCount, 'service');
        }

        if (!$request->has('unit')) {
            $defaults['unit'] = array_fill(0, $itemCount, 'Paket');
        }

        if ($defaults !== []) {
            $request->merge($defaults);
        }
    }

}
