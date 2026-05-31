<?php

namespace App\Http\Controllers\Admin;

use App\Models\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::latest()
            ->paginate(10);

        return view(
            'admin.clients.index',
            compact('clients')
        );
    }

    public function create()
    {
        return view('admin.clients.create');
    }

    public function store(Request $request)
    {
        $request->validate([

            'name' => 'required',
            'phone' => $request->phone,

        ]);

        Client::create($request->all());

        return redirect()
            ->route('clients.index')
            ->with(
                'success',
                'Client berhasil dibuat'
            );
    }

    public function edit(Client $client)
    {
        return view(
            'admin.clients.edit',
            compact('client')
        );
    }

    public function update(
        Request $request,
        Client $client
    ) {

        $request->validate([

            'name' => 'required',

        ]);

        $client->update($request->all());

        return redirect()
            ->route('clients.index')
            ->with(
                'success',
                'Client berhasil diupdate'
            );
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return back()->with(
            'success',
            'Client berhasil dihapus'
        );
    }


}
