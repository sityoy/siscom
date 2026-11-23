<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agreement;
use App\Models\CompanySetting;
use App\Models\Invoice;
use App\Support\JobTitleTranslator;
use App\Support\Pagination;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AgreementController extends Controller
{
    public function index(): View
    {
        return view('admin.agreements.index', [
            'agreements' => Agreement::latest('id')->paginate(Pagination::perPage()),
        ]);
    }

    public function create(Invoice $invoice): View|RedirectResponse
    {
        if ($agreement = $invoice->agreement) {
            return to_route('agreements.show', $agreement);
        }

        if ($invoice->status === 'cancelled') {
            return to_route('invoices.index')->with('error', 'Invoice yang dibatalkan tidak dapat dipakai untuk membuat MoU.');
        }

        $invoice->load(['client', 'project', 'items']);
        $company = CompanySetting::first();
        $today = today()->toDateString();

        return view('admin.agreements.form', [
            'invoice' => $invoice,
            'agreement' => new Agreement([
                'title' => Str::limit('Kerja Sama '.($invoice->project?->title ?: $invoice->type_label), 180, ''),
                'document_date' => $today,
                'city' => $company?->city ?: 'Tangerang',
                'scope' => Str::limit($invoice->items->pluck('description')->filter()->implode("\n"), 2200),
                'provider_signer' => $company?->director_name,
                'provider_title' => JobTitleTranslator::translate($company?->director_title, 'id'),
                'client_signer' => $invoice->client->authorized_official_name,
                'client_title' => JobTitleTranslator::translate($invoice->client->authorized_official_title, 'id'),
            ]),
        ]);
    }

    public function store(Request $request, Invoice $invoice): RedirectResponse
    {
        $data = $this->validatedTerms($request);

        $agreement = DB::transaction(function () use ($invoice, $data): ?Agreement {
            $invoice = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();

            if ($agreement = $invoice->agreement) {
                return $agreement;
            }

            if ($invoice->status === 'cancelled') {
                return null;
            }

            $company = CompanySetting::first();
            $provider = $company?->legal_name ?: ($company?->company_name ?: 'PT SITYOY IT SOLUTIONS');

            return $invoice->agreement()->create($data + [
                'invoice_reference' => $invoice->invoice_number,
                'invoice_date' => $invoice->invoice_date ?? $invoice->created_at,
                'invoice_total' => $invoice->grand_total,
                'provider_name' => $provider,
                'client_name' => $invoice->client->name,
                'letterhead' => [
                    'legal_name' => $provider,
                    'company_address' => $company?->company_address,
                    'company_email' => $company?->company_email,
                    'company_phone' => $company?->company_phone,
                    'website' => $company?->website,
                ],
            ]);
        }, 3);

        return $agreement
            ? to_route('agreements.show', $agreement)
            : to_route('invoices.index')->with('error', 'Invoice yang dibatalkan tidak dapat dipakai untuk membuat MoU.');
    }

    public function show(Agreement $agreement): View
    {
        return view('admin.agreements.form', compact('agreement'));
    }

    public function update(Request $request, Agreement $agreement): RedirectResponse
    {
        $data = $this->validatedTerms($request);

        $saved = DB::transaction(function () use ($agreement, $data): bool {
            $agreement = Agreement::whereKey($agreement->id)->lockForUpdate()->firstOrFail();

            return $agreement->finalized_at === null && $agreement->update($data);
        }, 3);

        return to_route('agreements.show', $agreement)->with(
            $saved ? 'success' : 'error',
            $saved ? 'Draf MoU berhasil disimpan.' : 'MoU final sudah terkunci.',
        );
    }

    public function finalize(Agreement $agreement): RedirectResponse
    {
        $finalized = DB::transaction(function () use ($agreement): bool {
            $agreement = Agreement::whereKey($agreement->id)->lockForUpdate()->firstOrFail();

            if ($agreement->finalized_at) {
                return true;
            }

            if (! $agreement->invoice || $agreement->invoice->status === 'cancelled') {
                return false;
            }

            return $agreement->forceFill(['finalized_at' => now()])->save();
        }, 3);

        return to_route('agreements.show', $agreement)->with(
            $finalized ? 'success' : 'error',
            $finalized ? 'MoU final siap dicetak untuk ditandatangani.' : 'Finalisasi memerlukan invoice sumber yang masih aktif.',
        );
    }

    public function pdf(Agreement $agreement): Response
    {
        $prefix = $agreement->finalized_at ? 'MoU' : 'Draf-MoU';

        return Pdf::loadView('admin.agreements.pdf', [
            'agreement' => $agreement,
            'company' => (object) $agreement->letterhead,
            'logoPath' => public_path('logo/siscombg2.png'),
        ])->setPaper('a4')->download($prefix.'-'.str_replace('/', '-', $agreement->number).'.pdf');
    }

    private function validatedTerms(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'document_date' => ['required', 'date_format:Y-m-d'],
            'starts_on' => ['required', 'date_format:Y-m-d'],
            'ends_on' => ['required', 'date_format:Y-m-d', 'after_or_equal:starts_on'],
            'city' => ['required', 'string', 'max:100'],
            'scope' => ['required', 'string', 'max:2400'],
            'payment_terms' => ['required', 'string', 'max:1600'],
            'additional_terms' => ['nullable', 'string', 'max:2400'],
            'provider_signer' => ['required', 'string', 'max:150'],
            'provider_title' => ['required', 'string', 'max:150'],
            'client_signer' => ['required', 'string', 'max:150'],
            'client_title' => ['required', 'string', 'max:150'],
        ], [
            'ends_on.after_or_equal' => 'Akhir masa berlaku tidak boleh lebih awal dari awal masa berlaku.',
        ], [
            'title' => 'judul kerja sama', 'document_date' => 'tanggal MoU', 'starts_on' => 'awal masa berlaku',
            'ends_on' => 'akhir masa berlaku', 'city' => 'kota penandatanganan', 'scope' => 'ruang lingkup',
            'payment_terms' => 'ketentuan pembayaran', 'additional_terms' => 'ketentuan tambahan',
            'provider_signer' => 'nama penandatangan penyedia', 'provider_title' => 'jabatan penyedia',
            'client_signer' => 'nama penandatangan client', 'client_title' => 'jabatan client',
        ]);
    }
}
