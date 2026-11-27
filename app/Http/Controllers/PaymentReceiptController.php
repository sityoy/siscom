<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaymentReceiptController extends Controller
{
    public function admin(Payment $payment): Response
    {
        return $this->download($payment);
    }

    public function client(Request $request, Payment $payment): Response
    {
        $client = $request->user()->client;
        abort_unless($client && $payment->invoice?->client_id == $client->id, 404);

        return $this->download($payment);
    }

    private function download(Payment $payment): Response
    {
        $payment->loadMissing('invoice.client');
        abort_unless($payment->invoice?->client, 404);

        $company = CompanySetting::first();
        $receiptNumber = 'NP-'.str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT);

        return Pdf::loadView('payments.receipt', compact('payment', 'company', 'receiptNumber'))
            ->setPaper('a5')
            ->download("nota-pembayaran-{$receiptNumber}.pdf");
    }
}
