<?php

namespace App\Http\Controllers\Admin;

use App\Models\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use App\Models\User;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::withCount([
            'projects',
            'invoices'
        ])
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
            'phone' => 'nullable|string|max:20',

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

            'address' => $request->address,

        ]);
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
            'phone' => 'nullable|string|max:20',

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

            'address' => $request->address,

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
}
