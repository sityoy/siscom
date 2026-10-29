<?php

namespace App\Http\Controllers\Client;

use App\Models\Invoice;
use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\CompanySetting;
use Illuminate\Http\Request;

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

    public function pdf(Request $request, Invoice $invoice)
    {
        $client = auth()->user()->client;

        if (!$client) {

            abort(403);

        }

        // CEK KEPEMILIKAN
        if ($invoice->client_id != $client->id) {

            abort(403);

        }

        $invoice->loadMissing(['client.user', 'project', 'items']);
        $company = CompanySetting::first();
        $language = in_array($request->query('lang'), ['id', 'en'], true)
            ? $request->query('lang')
            : 'id';

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
}
