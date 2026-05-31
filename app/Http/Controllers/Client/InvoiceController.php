<?php

namespace App\Http\Controllers\Client;

use App\Models\Invoice;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function index()
    {
        $client = auth()->user()->client;

        if (!$client) {

            abort(403);

        }

        $invoices = Invoice::where(

            'client_id',

            $client->id

        )->latest()->paginate(10);

        return view(

            'clients.invoices.index',

            compact('invoices')

        );
    }

    public function pdf(Invoice $invoice)
    {
        $client = auth()->user()->client;

        if (!$client) {

            abort(403);

        }

        // CEK KEPEMILIKAN
        if ($invoice->client_id != $client->id) {

            abort(403);

        }

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
}
