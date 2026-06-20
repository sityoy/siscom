<?php

namespace App\Http\Controllers\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ClientProfileController extends Controller
{
    public function index()
    {
        $client = auth()->user()->client;

        if (!$client) {

            abort(403);

        }

        return view(

            'clients.profile.index',

            compact('client')

        );
    }

    public function update(Request $request)
    {
        $client = auth()->user()->client;

        if (!$client) {

            abort(403);

        }

        $request->validate([

            'name' => 'required',

            'email' => 'required|email',

        ]);

        $client->update([

            'name' => $request->name,

            'email' => $request->email,

            'phone' => $request->phone,

            'company' => $request->company,

            'address' => $request->address,

        ]);

        return back()->with(

            'success',

            'Profile berhasil diupdate'

        );
    }
}
