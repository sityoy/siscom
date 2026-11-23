<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Agreement;
use App\Services\AgreementPdf;
use App\Support\Pagination;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AgreementController extends Controller
{
    public function index(Request $request): View
    {
        return view('clients.agreements.index', [
            'agreements' => $this->agreements($request)->latest('finalized_at')->orderByDesc('id')
                ->paginate(Pagination::perPage()),
        ]);
    }

    public function show(Request $request, string $agreementId): View
    {
        return view('clients.agreements.show', [
            'agreement' => $this->agreements($request)->findOrFail($agreementId),
        ]);
    }

    public function pdf(Request $request, string $agreementId, AgreementPdf $pdf): Response
    {
        return $pdf->download($this->agreements($request)->findOrFail($agreementId))
            ->header('Cache-Control', 'private, no-store');
    }

    private function agreements(Request $request): Builder
    {
        $client = $request->user()->client;
        abort_unless($client, 403);

        return Agreement::visibleToClient($client->id);
    }
}
