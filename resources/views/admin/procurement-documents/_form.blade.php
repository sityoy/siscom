@php
    $selectedProjectId = old('project_id', $document->project_id);
    $selectedInvoiceId = old('invoice_id', $document->invoice_id);
@endphp

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Periksa kembali data berikut:</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="alert alert-info">
    <i class="fas fa-info-circle mr-1"></i>
    Dokumen baru langsung berstatus <strong>Siap Digunakan</strong>, tanpa tahap Draft dan tanpa watermark.
    Data penandatangan sekolah otomatis mengikuti Pejabat Berwenang pada data Client dan tetap dapat disesuaikan.
</div>

<div class="row">
    <div class="col-md-7 mb-3">
        <label>Project <span class="text-danger">*</span></label>
        <select name="project_id" id="project_id" class="form-control" required>
            <option value="">-- Pilih Client dan Project --</option>
            @foreach($projects as $project)
                <option value="{{ $project->id }}"
                        data-budget="{{ $project->budget }}"
                        data-school-signer-name="{{ $project->client->authorized_official_name }}"
                        data-school-signer-title="{{ $project->client->authorized_official_title }}"
                        @selected((string) $selectedProjectId === (string) $project->id)>
                    {{ $project->client->name }} — {{ $project->title }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-5 mb-3">
        <label>Invoice Terkait</label>
        <select name="invoice_id" id="invoice_id" class="form-control">
            <option value="">-- Tidak ada/belum dibuat --</option>
            @foreach($projects as $project)
                @foreach($project->invoices as $invoice)
                    <option value="{{ $invoice->id }}" data-project="{{ $project->id }}"
                            @selected((string) $selectedInvoiceId === (string) $invoice->id)>
                        {{ $invoice->invoice_number }} — Rp {{ number_format($invoice->total_due, 0, ',', '.') }}
                    </option>
                @endforeach
            @endforeach
        </select>
    </div>

    <div class="col-md-4 mb-3">
        <label>Jenis Dokumen <span class="text-danger">*</span></label>
        <select name="type" class="form-control" required>
            @foreach(\App\Models\ProcurementDocument::TYPES as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $document->type) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4 mb-3">
        <label>Nomor Dokumen</label>
        <input type="text" name="document_number" class="form-control"
               value="{{ old('document_number', $document->exists ? $document->document_number : '') }}"
               placeholder="Kosongkan untuk nomor otomatis">
    </div>

    <div class="col-md-4 mb-3">
        <label>Tanggal Dokumen <span class="text-danger">*</span></label>
        <input type="date" name="document_date" class="form-control" required
               value="{{ old('document_date', optional($document->document_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
    </div>

    <div class="col-md-4 mb-3">
        <label>Status <span class="text-danger">*</span></label>
        <select name="status" class="form-control" required>
            @foreach(\App\Models\ProcurementDocument::STATUSES as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $document->status ?: 'ready') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-8 mb-3">
        <label>Perihal/Judul</label>
        <input type="text" name="subject" class="form-control"
               value="{{ old('subject', $document->subject) }}"
               placeholder="Contoh: Pengadaan dan Pengembangan Sistem SPMB Berbasis Online">
    </div>

    <div class="col-md-4 mb-3">
        <label>Nilai Pekerjaan <span class="text-danger">*</span></label>
        <input type="number" name="amount" id="amount" min="0" step="1" class="form-control" required
               value="{{ old('amount', $document->amount ?? 0) }}">
    </div>

    <div class="col-md-4 mb-3">
        <label>Mulai Pekerjaan</label>
        <input type="date" name="work_start_date" class="form-control"
               value="{{ old('work_start_date', optional($document->work_start_date)->format('Y-m-d')) }}">
    </div>

    <div class="col-md-4 mb-3">
        <label>Selesai Pekerjaan</label>
        <input type="date" name="work_end_date" class="form-control"
               value="{{ old('work_end_date', optional($document->work_end_date)->format('Y-m-d')) }}">
    </div>
</div>

<hr>
<h5 class="font-weight-bold">Penandatangan</h5>
<div class="row">
    <div class="col-md-3 mb-3">
        <label>Nama dari Sekolah</label>
        <input type="text" name="school_signer_name" id="school_signer_name" class="form-control"
               value="{{ old('school_signer_name', $document->school_signer_name) }}">
    </div>
    <div class="col-md-3 mb-3">
        <label>Jabatan di Sekolah</label>
        <input type="text" name="school_signer_title" id="school_signer_title" class="form-control"
               value="{{ old('school_signer_title', $document->school_signer_title) }}"
               placeholder="Kepala Sekolah/Bendahara">
    </div>
    <div class="col-md-3 mb-3">
        <label>Nama dari SIS.COM</label>
        <input type="text" name="provider_signer_name" class="form-control"
               value="{{ old('provider_signer_name', $document->provider_signer_name) }}">
    </div>
    <div class="col-md-3 mb-3">
        <label>Jabatan di SIS.COM</label>
        <input type="text" name="provider_signer_title" class="form-control"
               value="{{ old('provider_signer_title', $document->provider_signer_title) }}"
               placeholder="Direktur/Pimpinan">
    </div>
</div>

<hr>
<h5 class="font-weight-bold">Sumber Dana dan Perpajakan</h5>
<div class="alert alert-light border">
    Nilai pajak tidak dihitung otomatis karena perlakuannya bergantung pada SIPLah/non-SIPLah,
    jenis transaksi, serta status pajak penyedia. Isikan sesuai bukti transaksi dan ketentuan yang berlaku.
</div>
<div class="row">
    <div class="col-md-3 mb-3">
        <label>Sumber Dana</label>
        <select name="funding_source" class="form-control">
            @foreach(['' => '-- Pilih --', 'BOSP' => 'BOSP/BOS', 'Yayasan' => 'Yayasan', 'Komite' => 'Komite', 'Mandiri' => 'Mandiri/Non-anggaran', 'Lainnya' => 'Lainnya'] as $value => $label)
                <option value="{{ $value }}" @selected(old('funding_source', $document->funding_source) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 mb-3">
        <label>Metode Transaksi</label>
        <select name="transaction_method" class="form-control">
            <option value="non_siplah" @selected(old('transaction_method', $document->transaction_method) === 'non_siplah')>Non-SIPLah/Langsung</option>
            <option value="siplah" @selected(old('transaction_method', $document->transaction_method) === 'siplah')>SIPLah</option>
        </select>
    </div>
    <div class="col-md-3 mb-3">
        <label>Jenis Transaksi</label>
        <select name="transaction_type" class="form-control">
            @foreach(['' => '-- Pilih --', 'goods' => 'Barang', 'services' => 'Jasa', 'rental' => 'Sewa', 'other' => 'Lainnya'] as $value => $label)
                <option value="{{ $value }}" @selected(old('transaction_type', $document->transaction_type) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 mb-3">
        <label>Status Pajak Penyedia</label>
        <select name="provider_tax_status" class="form-control">
            @foreach(['' => '-- Pilih --', 'pkp' => 'PKP', 'non_pkp' => 'Non-PKP', 'umkm_final' => 'UMKM Pajak Final', 'other' => 'Lainnya'] as $value => $label)
                <option value="{{ $value }}" @selected(old('provider_tax_status', $document->provider_tax_status) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label>Dasar Pengenaan Pajak (DPP)</label>
        <input type="number" name="tax_base" min="0" class="form-control" value="{{ old('tax_base', $document->tax_base ?? 0) }}">
    </div>
    <div class="col-md-4 mb-3">
        <label>PPN</label>
        <input type="number" name="vat_amount" min="0" class="form-control" value="{{ old('vat_amount', $document->vat_amount ?? 0) }}">
    </div>
    <div class="col-md-4 mb-3">
        <label>PPh/Potongan Pajak</label>
        <input type="number" name="income_tax_amount" min="0" class="form-control" value="{{ old('income_tax_amount', $document->income_tax_amount ?? 0) }}">
    </div>
</div>

<div class="mb-3">
    <label>Ketentuan Pekerjaan/Pembayaran</label>
    <textarea name="terms" class="form-control" rows="4" placeholder="Satu ketentuan per baris">{{ old('terms', $document->terms) }}</textarea>
</div>

<div class="mb-3">
    <label>Catatan Tambahan</label>
    <textarea name="notes" class="form-control" rows="3">{{ old('notes', $document->notes) }}</textarea>
</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const project = document.getElementById('project_id');
    const invoice = document.getElementById('invoice_id');
    const amount = document.getElementById('amount');
    const schoolSignerName = document.getElementById('school_signer_name');
    const schoolSignerTitle = document.getElementById('school_signer_title');

    function fillSchoolSigner(force) {
        const option = project.options[project.selectedIndex];
        if (!option) return;

        if (force || !schoolSignerName.value) {
            schoolSignerName.value = option.dataset.schoolSignerName || '';
        }
        if (force || !schoolSignerTitle.value) {
            schoolSignerTitle.value = option.dataset.schoolSignerTitle || '';
        }
    }

    function filterInvoices() {
        const selectedProject = project.value;
        Array.from(invoice.options).forEach(function (option, index) {
            if (index === 0) return;
            option.hidden = option.dataset.project !== selectedProject;
            if (option.hidden && option.selected) invoice.value = '';
        });
    }

    project.addEventListener('change', function () {
        const option = project.options[project.selectedIndex];
        if ((!amount.value || Number(amount.value) === 0) && option.dataset.budget) {
            amount.value = option.dataset.budget;
        }
        fillSchoolSigner(true);
        filterInvoices();
    });

    filterInvoices();
    fillSchoolSigner(false);
});
</script>
@endpush
