<?php

namespace App\Http\Controllers\Admin;

use App\Models\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Carbon\Carbon;

class ClientController extends Controller
{
public function index(Request $request)
{
    $clients = Client::withCount([
        'projects',
        'invoices'
    ]);

    $clients = $clients
        ->latest()
        ->paginate(10);

    return view(
        'admin.clients.index',
        compact('clients')
    );
}

    public function create()
    {
       $users = User::role('client')
            ->whereDoesntHave('client')
            ->orderBy('name')
            ->get();

        return view(
            'admin.clients.create',
            compact('users')
        );
    }

    // public function create()
    // {
    //     return view('admin.clients.create');
    // }

    public function store(Request $request)
    {
        $request->validate([

        'user_id' => 'required|exists:users,id',
        'phone' => [
            'nullable',
            'string',
            'regex:/^62[0-9]{8,15}$/',
        ],
        'phone_2' => [
            'nullable',
            'string',
            'regex:/^62[0-9]{8,15}$/',
            'different:phone',
        ],
        'phone_3' => [
            'nullable',
            'string',
            'regex:/^62[0-9]{8,15}$/',
            'different:phone',
            'different:phone_2',
        ],

        'package_name' => 'nullable|string|max:100',

        'package_price' => 'nullable|numeric',

        'subscription_start' => 'nullable|date',

        'subscription_end' => 'nullable|date',

        'grace_period_days' => 'nullable|integer|min:0',

        ]);

        $user = User::findOrFail(
            $request->user_id
        );

        Client::create([

            'user_id' => $user->id,

            'name' => $user->name,

            'email' => $user->email,

            'company' => $request->company,

            'phone' => $request->phone,

            'phone_2' => $request->phone_2,

            'phone_3' => $request->phone_3,

            'address' => $request->address,

            'package_name' => $request->package_name,

            'package_price' => $request->package_price,

            'subscription_start' => $request->subscription_start,

            'subscription_end' => $request->subscription_end,

            'grace_period_days' => $request->grace_period_days ?? 7,

        ]);
        
        return redirect()
        ->route('clients.index')
        ->with(
            'success',
            'Client berhasil ditambahkan'
        );
    }

    public function edit(Client $client)
    {
        $users = User::role('client')
            ->orderBy('name')
            ->get();

        return view(
            'admin.clients.edit',
            compact(
                'client',
                'users'
            )
        );
    }

    public function update(
        Request $request,
        Client $client
    ) {

        $request->validate([

            'user_id' => 'required|exists:users,id',
            'phone' => [
                'nullable',
                'string',
                'regex:/^62[0-9]{8,15}$/',
            ],
            'phone_2' => [
                'nullable',
                'string',
                'regex:/^62[0-9]{8,15}$/',
                'different:phone',
            ],
            'phone_3' => [
                'nullable',
                'string',
                'regex:/^62[0-9]{8,15}$/',
                'different:phone',
                'different:phone_2',
            ],

        ]);

        $user = User::findOrFail(
            $request->user_id
        );

        $client->update([

            'user_id' => $user->id,

            'name' => $user->name,

            'email' => $user->email,

            'company' => $request->company,

            'phone' => $request->phone,

            'phone_2' => $request->phone_2,

            'phone_3' => $request->phone_3,

            'address' => $request->address,

            'package_name' => $request->package_name,

            'package_price' => $request->package_price,

            'subscription_start' => $request->subscription_start,

            'subscription_end' => $request->subscription_end,

            'grace_period_days' => $request->grace_period_days ?? 7,

        ]);

        return redirect()
            ->route('clients.index')
            ->with(
                'success',
                'Client berhasil diupdate'
            );
    }

    public function destroy(Client $client)
    {
        if (
            $client->projects()->count() > 0 ||
            $client->invoices()->count() > 0 ||
            $client->tickets()->count() > 0
        ) {

            return back()->with(
                'error',
                'Client masih memiliki data aktif'
            );
        }

        $client->delete();

        return back()->with(
            'success',
            'Client berhasil dihapus'
        );
    }

    public function renew(Client $client)
    {
        if (!$client->package_name) {

            return back()->with(
                'error',
                'Client belum memiliki paket langganan'
            );
        }

        $invoice = Invoice::create([

            'client_id'      => $client->id,

            'project_id'     => null,

            'invoice_number' =>
                'INV-' .
                now()->format('YmdHis'),

            'subtotal'       =>
                $client->package_price,

            'vat_percent'    => 0,

            'vat'            => 0,

            'service_fee'    => 0,

            'cashback'       => 0,

            'grand_total'    =>
                $client->package_price,

            'due_date'       =>
                now()->addDays(7),

            'status'         => 'unpaid',

            'notes'          =>
                'Invoice Perpanjangan Paket ' .
                $client->package_name,

                'invoice_type' => 'renewal',

        ]);

        $duration = 1;
        $durationType = 'Tahun';

        InvoiceItem::create([

            'invoice_id' => $invoice->id,

            'description' =>
                'Perpanjangan Paket ' .
                $client->package_name,

            'qty' => 1,

            'price' => $client->package_price,

            'total' => $client->package_price,

            'duration' => 1,

            'duration_type' => 'Tahun',

            'start_date' => now()->toDateString(),

            'end_date' => now()
                ->addYear()
                ->toDateString(),

        ]);

        return redirect()
            ->route('invoices.edit', $invoice->id)
            ->with(
                'success',
                'Invoice renewal berhasil dibuat'
            );
    }
}
