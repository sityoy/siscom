<?php

namespace App\Http\Controllers\Admin;

use App\Models\Client;
use App\Models\CompanySetting;
use App\Models\Invoice;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\InvoiceItem;
use App\Mail\InvoiceMail;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Notification;

class InvoiceController extends Controller
{
    public function index(Request $request)
{
    $query = Invoice::with([
        'client',
        'project'
    ]);

    if ($request->type) {

        $query->where(
            'invoice_type',
            $request->type
        );
    }

    $invoices = $query
        ->latest()
        ->paginate(10);

    return view(
        'admin.invoices.index',
        compact('invoices')
    );
}

    public function create(Request $request)
    {
        $clients = Client::all();

        $projects = Project::all();

        $selectedProject = null;

        if ($request->filled('project_id')) {
            $selectedProject = Project::with('client')
                ->find($request->integer('project_id'));
        }

        $invoiceDefaults = [
            'client_id' => $selectedProject?->client_id,
            'project_id' => $selectedProject?->id,
            'invoice_type' => 'project',
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'description' => $selectedProject
                ? 'Biaya Project - ' . $selectedProject->title
                : '',
            'price' => $selectedProject?->budget,
            'duration' => 1,
            'duration_type' => 'Hari',
            'start_date' => null,
            'end_date' => null,
            'vat_percent' => 11,
            'service_fee' => 10000,
            'late_fee_active' => (bool) ($selectedProject?->late_fee_active),
            'late_fee_per_month' => $selectedProject?->late_fee_per_month
                ?? 100000,
            'notes' => $selectedProject
                ? 'Invoice Project ' . $selectedProject->title
                : '',
        ];

        return view(
            'admin.invoices.create',
            compact(
                'clients',
                'projects',
                'invoiceDefaults'
            )
        );
    }

    public function store(Request $request)
    {
        $request->validate([

            'client_id' => 'required|exists:clients,id',

            'project_id' => 'nullable|exists:projects,id',

            'invoice_type' => 'required|in:project,renewal',

            'invoice_number' => 'required',

            'due_date' => 'required|date',

            'status' => 'required',

            'vat_percent' => 'nullable|numeric|min:0|max:100',

            'cashback' => 'nullable|numeric|min:0|max:20',

            'service_fee' => 'nullable|numeric|min:0',

            'late_fee_active' => 'nullable|boolean',

            'late_fee_per_month' => 'nullable|required_if:late_fee_active,1|numeric|min:0',

            'description' => 'required|array|min:1',

            'description.*' => 'required|string|max:255',

            'qty' => 'required|array|min:1',

            'qty.*' => 'required|numeric|min:1',

            'price' => 'required|array|min:1',

            'price.*' => 'required|numeric|min:0',
        ]);

        $subtotal = 0;

        foreach ($request->price as $index => $price) {

            $qty = $request->qty[$index];

            $subtotal += $qty * $price;
        }

        $vatPercent = $request->vat_percent ?? 0;

        $vat = ($subtotal * $vatPercent) / 100;

        $serviceFee = $request->service_fee ?? 0;

        $cashback = $request->cashback ?? 0;

        // $cashbackAmount =
        //     ($subtotal + $vat + $serviceFee)
        //     * $cashback / 100;

        $grandTotal =
        $subtotal + $vat + $serviceFee;


        $invoice = Invoice::create([

            'client_id' => $request->client_id,

            'project_id' => $request->project_id,

            'invoice_number' => $request->invoice_number,

            'invoice_type' => $request->invoice_type,

            'subtotal' => $subtotal,

            'vat_percent' => $vatPercent,

            'vat' => $vat,

            'service_fee' => $serviceFee,

            'late_fee_active' => $request->boolean('late_fee_active'),

            'late_fee_per_month' => $request->boolean('late_fee_active')
                ? $request->late_fee_per_month
                : 0,

            'cashback' => $cashback,

            'grand_total' => $grandTotal,

            'due_date' => $request->due_date,

            'status' => $request->status,

            'paid_at' => $request->status === 'paid'
                ? now()
                : null,

            'notes' => $request->notes,

        ]);

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

            $total = $qty * $price;

            InvoiceItem::create([

                'invoice_id' => $invoice->id,

                'description' => $description,

                'qty' => $qty,

                'price' => $price,

                'total' => $total,

                'duration' => $request->duration[$index],

                'duration_type' => $request->duration_type[$index],

                'start_date' => $request->start_date[$index],

                'end_date' => $request->end_date[$index],

            ]);
        }

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

            $pdfUrl = route(
                'invoice.view',
                $invoice->id
            );

            WhatsAppService::sendDocument(
                $invoice->client->phone,
                $this->buildPaymentReminderMessage(
                    $invoice,
                    $pdfUrl
                ),
                $pdfUrl
            );

        } catch (\Exception $e) {

            // skip whatsapp error

        }

                return redirect()
                ->route('invoices.index')
                ->with(
                    'success',
                    'Invoice berhasil dibuat'
                );


            }

    public function edit(Invoice $invoice)
    {
        $clients = Client::all();

        $projects = Project::all();

        return view(
            'admin.invoices.edit',
            compact(
                'invoice',
                'clients',
                'projects'
            )
        );
    }

    public function update(Request $request, Invoice $invoice)
    {
            $request->validate([
                'client_id' => 'required|exists:clients,id',
                'project_id' => 'nullable|exists:projects,id',
                'invoice_number' => 'required',
                'due_date' => 'required|date',
                'status' => 'required',
                'vat_percent' => 'required|numeric|min:0|max:100',
                'cashback' => 'nullable|numeric|min:0|max:20',
                'invoice_type' => 'required|in:project,renewal',
                'late_fee_active' => 'nullable|boolean',
                'late_fee_per_month' => 'nullable|required_if:late_fee_active,1|numeric|min:0',
            ]);

            $subtotal = 0;

            foreach ($request->price as $index => $price) {

                $qty = $request->qty[$index];

                $subtotal += $qty * $price;
            }

            $vatPercent = $request->vat_percent ?? 0;

            $vat = ($subtotal * $vatPercent) / 100;

            $serviceFee = $request->service_fee ?? 0;

            $cashback = $request->cashback ?? 0;

            // $cashbackAmount =
            //     ($subtotal + $vat + $serviceFee)
            //     * $cashback / 100;

            $grandTotal =
                $subtotal + $vat + $serviceFee;


            $invoice->update([

                'client_id' => $request->client_id,

                'project_id' => $request->project_id,

                'invoice_number' => $request->invoice_number,

                'invoice_type' => $request->invoice_type,

                'subtotal' => $subtotal,

                'vat_percent' => $vatPercent,

                'vat' => $vat,

                'service_fee' => $serviceFee,

                'late_fee_active' => $request->boolean('late_fee_active'),

                'late_fee_per_month' => $request->boolean('late_fee_active')
                    ? $request->late_fee_per_month
                    : 0,

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

                $total = $qty * $price;


                InvoiceItem::create([

                    'invoice_id' => $invoice->id,

                    'description' => $description,

                    'qty' => $qty,

                    'price' => $price,

                    'total' => $total,

                    'duration' => $request->duration[$index],

                    'duration_type' => $request->duration_type[$index],

                    'start_date' => $request->start_date[$index],

                    'end_date' => $request->end_date[$index],

                ]);


            }

            return redirect()
                ->route('invoices.index')
                ->with(
                    'success',
                    'Invoice berhasil diupdate'
                );
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->items()->delete();

        $invoice->delete();

        return back()->with(
            'success',
            'Invoice berhasil dihapus'
        );
    }

    public function pdf(Invoice $invoice)
    {
        $pdf = Pdf::loadView(
            'admin.invoices.pdf',
            compact('invoice')

        );


        return $pdf->download(
            'invoice-' .
            $invoice->invoice_number .
            '.pdf'
        );


    }

    public function viewPdf(
        Invoice $invoice
    ){
        $pdf = Pdf::loadView(
            'admin.invoices.pdf',
            compact('invoice')
        );

        return $pdf->stream();
    }

    private function buildPaymentReminderMessage(
        Invoice $invoice,
        string $pdfUrl
    ): string {
        $invoice->loadMissing([
            'client',
            'project',
        ]);

        $setting = CompanySetting::first();

        $companyName = $setting?->company_name ?: 'SIS.COM';
        $senderName = 'Lena Septiana';
        $clientName = $invoice->client->company
            ?: $invoice->client->name;
        $projectName = $invoice->project?->title
            ?: 'Layanan SIS.COM';

        $dueDate = $invoice->due_date
            ->copy()
            ->startOfDay();
        $today = now()->startOfDay();

        if ($invoice->status === 'paid') {
            $timeStatus = 'LUNAS';
        } elseif ($today->gt($dueDate)) {
            $daysLate = (int) $dueDate->diffInDays($today);
            $timeStatus = "TERLAMBAT {$daysLate} HARI";
        } elseif ($today->equalTo($dueDate)) {
            $timeStatus = 'JATUH TEMPO HARI INI';
        } else {
            $daysRemaining = (int) $today->diffInDays($dueDate);
            $timeStatus = "TERSISA {$daysRemaining} HARI";
        }

        $dueDateLabel = $dueDate
            ->locale('id')
            ->translatedFormat('d F Y');
        $statusLabel = strtoupper($invoice->status);
        $totalDue = number_format(
            $invoice->total_due,
            0,
            ',',
            '.'
        );

        $lateFeeLine = '';

        if ($invoice->late_fee_amount > 0) {
            $lateFee = number_format(
                $invoice->late_fee_amount,
                0,
                ',',
                '.'
            );

            $lateFeeLine = "\n💸 *Denda ({$invoice->late_months} bulan): Rp {$lateFee}*\n";
        }

        $bankAccountName = $setting?->bank_bca_name
            ?: $setting?->bank_jakarta_name
            ?: strtoupper($senderName);

        $bankAccounts = collect([
            [
                'name' => 'BCA',
                'number' => $setting?->bank_bca,
            ],
            [
                'name' => 'Bank Jakarta',
                'number' => $setting?->bank_jakarta,
            ],
        ])->filter(
            fn (array $bank) => filled($bank['number'])
        )->values()->map(
            fn (array $bank, int $index) =>
                ($index + 1) . ". {$bank['name']}: {$bank['number']}"
        )->implode("\n");

        if ($bankAccounts === '') {
            $bankAccounts = 'Silakan hubungi admin untuk informasi rekening.';
        }

        return "⚠️ *PENGINGAT PEMBAYARAN " . strtoupper($projectName) . "* ⚠️

Halo Bapak/Ibu Pimpinan dan Admin *{$clientName}*,

Perkenalkan, saya *{$senderName}* dari *{$companyName}*. Kami mohon izin untuk menyampaikan tagihan layanan *{$projectName}* yang saat ini berstatus *{$statusLabel}*.

📝 *No. Invoice:* {$invoice->invoice_number}
⏳ *Batas Pembayaran:* {$dueDateLabel}
🚨 *Status Waktu:* *{$timeStatus}*
{$lateFeeLine}
💰 *TOTAL TAGIHAN: Rp {$totalDue}*

📄 *Lihat Rincian Invoice:* {$pdfUrl}

💳 *Metode Pembayaran (a/n " . strtoupper($bankAccountName) . "):*
{$bankAccounts}

Mohon kesediaannya untuk segera menyelesaikan administrasi pembayaran dan mengirimkan bukti transfer kepada kami. Silakan abaikan pesan ini apabila Bapak/Ibu telah melakukan pembayaran.

Terima kasih atas kerja sama yang terjalin dengan baik,
*{$companyName} - Software House & IT Solutions*";
    }

}