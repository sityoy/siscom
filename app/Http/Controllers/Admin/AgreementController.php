<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agreement;
use App\Models\CompanySetting;
use App\Models\Invoice;
use App\Services\AgreementPdf;
use App\Support\JobTitleTranslator;
use App\Support\Pagination;
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

    public function create(Request $request, Invoice $invoice): View|RedirectResponse
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
        $cbt = $request->query('template') === 'cbt';
        $scope = $invoice->items->pluck('description')->filter()->implode("\n");
        $details = [
            'provider_address' => $company?->company_address,
            'client_address' => $invoice->client->address,
        ];
        $paymentTerms = null;

        if ($cbt) {
            $scope = "Penggunaan platform Computer Based Test (CBT) untuk satu periode ujian sesuai jadwal dan ruang lingkup yang disepakati.\n\n".$scope;
            $details += [
                'fee_per_student' => 15000,
                'provider_obligations' => 'Pihak Pertama menyediakan akses CBT dan berhak menerima pembayaran sesuai kesepakatan. Pihak Pertama menjaga ketersediaan layanan dan membantu penanganan gangguan teknis yang dilaporkan oleh Pihak Kedua.',
                'client_obligations' => 'Pihak Kedua berhak menggunakan layanan CBT sesuai ruang lingkup dan masa berlaku. Pihak Kedua menyiapkan data peserta, soal, jadwal, perangkat, koneksi internet, serta panitia ujian, dan melakukan pembayaran sesuai ketentuan.',
                'support' => 'Kedua pihak menunjuk kontak teknis untuk persiapan, simulasi, dan penanganan kendala. Jadwal dukungan dan mekanisme pelaporan disepakati sebelum pelaksanaan ujian.',
                'confidentiality' => 'Kedua pihak menjaga kerahasiaan soal, kredensial, dan data peserta. Akses dibatasi kepada petugas yang berwenang. Penggunaan, penyimpanan, dan penghapusan data disepakati bersama sesuai kebutuhan penyelenggaraan ujian.',
                'disputes' => 'Perbedaan pendapat dibahas terlebih dahulu melalui musyawarah antara wakil kedua pihak. Tindak lanjut dicatat berdasarkan kesepakatan bersama.',
            ];
            $paymentTerms = 'Pembayaran dilakukan melalui transfer ke rekening Bank DKI atas nama perusahaan yang telah dikonfirmasi kedua pihak, paling lambat 4 (empat) hari kalender setelah tanggal penerbitan invoice.';

            $providerName = $company?->legal_name ?: ($company?->company_name ?: 'PT SITYOY IT SOLUTIONS');
            if (filled($company?->bank_jakarta) && Str::lower(trim((string) $company->bank_jakarta_name)) === Str::lower(trim($providerName))) {
                $paymentTerms = "Pembayaran dilakukan melalui transfer ke rekening Bank DKI paling lambat 4 (empat) hari kalender setelah tanggal penerbitan invoice.\nAtas nama {$company->bank_jakarta_name}\nNomor rekening {$company->bank_jakarta}";
            }
        }

        return view('admin.agreements.form', [
            'invoice' => $invoice,
            'cbtTemplate' => $cbt,
            'agreement' => new Agreement([
                'title' => $cbt ? 'Kerja Sama Sistem Ujian Online (CBT)' : Str::limit('Kerja Sama '.($invoice->project?->title ?: $invoice->type_label), 180, ''),
                'document_date' => $today,
                'city' => $company?->city ?: 'Tangerang',
                'scope' => Str::limit(trim($scope), 2200),
                'payment_terms' => $paymentTerms,
                'additional_terms' => $cbt ? 'Perubahan ruang lingkup, biaya, dan jadwal dituangkan dalam kesepakatan tertulis kedua pihak.' : null,
                'details' => $details,
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
                'client_id' => $invoice->client_id,
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

            if (! $agreement->invoice || $agreement->invoice->status === 'cancelled'
                || (int) $agreement->client_id !== (int) $agreement->invoice->client_id) {
                return false;
            }

            return $agreement->forceFill(['finalized_at' => now()])->save();
        }, 3);

        return to_route('agreements.show', $agreement)->with(
            $finalized ? 'success' : 'error',
            $finalized ? 'MoU final siap ditandatangani dan tersedia di portal client.' : 'Finalisasi memerlukan invoice aktif yang masih terhubung ke client yang sama.',
        );
    }

    public function pdf(Agreement $agreement, AgreementPdf $pdf): Response
    {
        return $pdf->download($agreement);
    }

    public function destroy(Agreement $agreement): RedirectResponse
    {
        $invoiceId = $agreement->invoice_id;
        $agreement->deleteOrFail();

        if ($invoiceId && Invoice::whereKey($invoiceId)->where('status', '!=', 'cancelled')->exists()) {
            return to_route('agreements.create', $invoiceId)
                ->with('success', 'MoU dihapus permanen. Bapak dapat membuat draf baru dari invoice ini.');
        }

        return to_route('agreements.index')->with('success', 'MoU dihapus permanen.');
    }

    private function validatedTerms(Request $request): array
    {
        $clauseRules = array_fill_keys(array_map(fn ($key) => 'details.'.$key, array_keys(Agreement::CLAUSES)), ['nullable', 'string', 'max:2400']);

        $data = $request->validate([
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
            'details' => ['nullable', 'array:'.implode(',', array_merge(array_keys(Agreement::CLAUSES), ['provider_address', 'client_address', 'student_count', 'fee_per_student']))],
            'details.provider_address' => ['nullable', 'string', 'max:350'],
            'details.client_address' => ['nullable', 'string', 'max:350'],
            'details.student_count' => ['nullable', 'required_with:details.fee_per_student', 'integer', 'min:1', 'max:1000000'],
            'details.fee_per_student' => ['nullable', 'required_with:details.student_count', 'integer', 'min:1', 'max:1000000000'],
        ] + $clauseRules, [
            'ends_on.after_or_equal' => 'Akhir masa berlaku tidak boleh lebih awal dari awal masa berlaku.',
            'details.student_count.required_with' => 'Isi jumlah peserta untuk menghitung biaya per periode.',
            'details.fee_per_student.required_with' => 'Isi tarif per siswa untuk menghitung biaya per periode.',
        ], [
            'title' => 'judul kerja sama', 'document_date' => 'tanggal MoU', 'starts_on' => 'awal masa berlaku',
            'ends_on' => 'akhir masa berlaku', 'city' => 'kota penandatanganan', 'scope' => 'ruang lingkup',
            'payment_terms' => 'ketentuan pembayaran', 'additional_terms' => 'ketentuan tambahan',
            'provider_signer' => 'nama penandatangan penyedia', 'provider_title' => 'jabatan penyedia',
            'client_signer' => 'nama penandatangan client', 'client_title' => 'jabatan client',
        ]);

        if (array_key_exists('details', $data)) {
            $data['details'] = array_filter($data['details'] ?? [], 'filled') ?: null;
        }

        return $data;
    }
}
