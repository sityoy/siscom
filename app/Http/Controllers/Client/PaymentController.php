<?php

namespace App\Http\Controllers\Client;

use App\Models\Payment;
use App\Http\Controllers\Controller;
use App\Models\AdminNotification;

class PaymentController extends Controller
{
    public function index()
    {
        $client = auth()->user()->client;

        $payments = Payment::whereHas(
            'invoice',
            function ($query) use ($client) {

                $query->where(
                    'client_id',
                    $client->id
                );

            }
        )->latest()->paginate(10);

                AdminNotification::create([

            'title' => 'Pembayaran Baru',

            'message' =>
                auth()->user()->client->name .
                ' mengupload pembayaran invoice #' .
                $invoice->id,

        ]);

        return view(
            'clients.payments.index',
            compact('payments')
        );
    }
}
