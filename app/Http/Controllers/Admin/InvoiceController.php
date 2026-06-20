<?php

namespace App\Http\Controllers\Admin;

use App\Models\Client;
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
    public function index()
    {
        $invoices = Invoice::with([
            'client',
            'project'
        ])->latest()->paginate(10);

        return view(
            'admin.invoices.index',
            compact('invoices')
        );
    }

    public function create()
    {
        $clients = Client::all();

        $projects = Project::all();

        return view(
            'admin.invoices.create',
            compact(
                'clients',
                'projects'
            )
        );
    }

    public function store(Request $request)
    {
        $subtotal = 0;

        foreach ($request->price as $index => $price) {

            $qty = $request->qty[$index];

            $subtotal += $qty * $price;
        }

        $request->validate([

            'client_id' => 'required|exists:clients,id',

            'project_id' => 'nullable|exists:projects,id',

            'invoice_number' => 'required',

            'due_date' => 'required|date',

            'status' => 'required',

            'vat_percent' => 'nullable|numeric|min:0|max:100',

            'cashback' => 'nullable|numeric|min:0|max:20',
        ]);

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

            'subtotal' => $subtotal,

            'vat_percent' => $vatPercent,

            'vat' => $vat,

            'service_fee' => $serviceFee,

            'cashback' => $cashback,

            'grand_total' => $grandTotal,

            'due_date' => $request->due_date,

            'status' => $request->status,

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

"📄 *INVOICE BARU SIS.COM*

Halo, {$invoice->client->name},
Invoice baru telah diterbitkan.

━━━━━━━━━━━━━━━

No Invoice :
{$invoice->invoice_number}

Project :
" . ($invoice->project->title ?? '-') . "

Tanggal Jatuh Tempo :
{$invoice->due_date}

Status :
" . strtoupper($invoice->status) . "

━━━━━━━━━━━━━━━

Subtotal :
Rp " . number_format(
$invoice->subtotal,
0,
',',
'.'
) . "

PPN (" . number_format($invoice->vat_percent,0) . "%)
Rp " . number_format(
$invoice->vat,
0,
',',
'.'
) . "

Service Fee :
Rp " . number_format(
$invoice->service_fee,
0,
',',
'.'
) . "

━━━━━━━━━━━━━━━

GRAND TOTAL :
Rp " . number_format(
$invoice->grand_total,
0,
',',
'.'
) . "

Catatan :
" . ($invoice->notes ?: '-') . "


Silakan login ke Portal Client:

https://sis.com/login

untuk melihat detail invoice dan status pembayaran.

Software House & IT Solutions

📎 Download Invoice:

{$pdfUrl}",

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

                'subtotal' => $subtotal,

                'vat_percent' => $vatPercent,

                'vat' => $vat,

                'service_fee' => $serviceFee,

                'cashback' => $cashback,

                'grand_total' => $grandTotal,

                'due_date' => $request->due_date,

                'status' => $request->status,

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

}
