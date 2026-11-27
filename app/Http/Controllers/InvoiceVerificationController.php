<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Response;

class InvoiceVerificationController extends Controller
{
    public function show(string $code): Response
    {
        $invoice = Invoice::where('verification_code', $code)->first();

        return response()
            ->view('invoices.verify', compact('invoice'), $invoice ? 200 : 404)
            ->header('Cache-Control', 'private, no-store')
            ->header('Referrer-Policy', 'no-referrer')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }
}
