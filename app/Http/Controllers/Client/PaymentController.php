<?php

namespace App\Http\Controllers\Client;

use App\Models\Payment;
use App\Http\Controllers\Controller;

class PaymentController extends Controller
{
    public function index()
    {
        $client = auth()->user()->client;

        if (!$client) {

            abort(403);

        }

        $payments = Payment::whereHas(
            'invoice',
            function ($query) use ($client) {

                $query->where(
                    'client_id',
                    $client->id
                );

            }
        )->latest()->paginate(10);

        return view(
            'clients.payments.index',
            compact('payments')
        );
    }
}
