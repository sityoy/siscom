<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\InvoiceApprovalArtwork;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InvoiceApprovalArtworkController extends Controller
{
    public function store(Request $request, InvoiceApprovalArtwork $artwork): RedirectResponse
    {
        $image = ['required', 'file', 'mimes:png', 'image', 'max:2048', 'dimensions:max_width=4096,max_height=4096'];
        $request->validateWithBag('invoiceArtwork', [
            'signature' => $image,
            'stamp' => $image,
        ], [], ['signature' => 'tanda tangan', 'stamp' => 'stempel']);

        $artwork->save($request->file('signature'), $request->file('stamp'));

        return redirect(route('settings.index').'#tanda-tangan-invoice')
            ->with('success', 'Tanda tangan dan stempel invoice telah disimpan.');
    }
}
