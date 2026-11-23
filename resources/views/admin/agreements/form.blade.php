@extends('layouts.admin')

@section('page-title', $agreement->exists ? 'MoU Kerja Sama' : 'Buat MoU dari Invoice')

@section('admin-content')
<div class="card">
    <div class="card-header d-flex align-items-center flex-wrap">
        <h2 class="card-title font-weight-bold mr-3">{{ $agreement->exists ? $agreement->number : 'Draf MoU Baru' }}</h2>
        @if($agreement->exists)
            <span class="badge badge-{{ $agreement->finalized_at ? 'success' : 'warning' }}">{{ $agreement->finalized_at ? 'Final' : 'Draf' }}</span>
            <a class="btn btn-sm btn-outline-success ml-auto" href="{{ route('agreements.pdf', $agreement) }}">
                {{ $agreement->finalized_at ? 'Unduh PDF Final' : 'Unduh PDF Draf' }}
            </a>
        @endif
    </div>
    <div class="card-body">
        <dl class="row mb-3">
            <dt class="col-sm-4">Invoice Sumber</dt>
            <dd class="col-sm-8">
                {{ $agreement->invoice_reference ?? $invoice->invoice_number }}
                @if($agreement->exists && !$agreement->invoice_id)
                    <span class="text-muted">(invoice sumber dihapus)</span>
                @endif
            </dd>
            <dt class="col-sm-4">Client</dt>
            <dd class="col-sm-8">{{ $agreement->client_name ?? $invoice->client->name }}</dd>
            <dt class="col-sm-4">Nilai Bruto Referensi</dt>
            <dd class="col-sm-8">Rp {{ number_format((float) ($agreement->invoice_total ?? $invoice->grand_total), 2, ',', '.') }}</dd>
        </dl>

        <form method="POST" action="{{ $agreement->exists ? route('agreements.update', $agreement) : route('agreements.store', $invoice) }}">
            @csrf
            @if($agreement->exists)
                @method('PUT')
            @endif
            <fieldset @disabled($agreement->finalized_at !== null)>
                <div class="form-group">
                    <label for="mou-title">Judul Kerja Sama</label>
                    <input id="mou-title" name="title" class="form-control" maxlength="180" required value="{{ old('title', $agreement->title) }}">
                </div>
                <div class="form-row">
                    @foreach(['document_date' => 'Tanggal MoU', 'starts_on' => 'Awal Masa Berlaku', 'ends_on' => 'Akhir Masa Berlaku'] as $field => $label)
                        <div class="form-group col-md-3">
                            <label for="mou-{{ $field }}">{{ $label }}</label>
                            <input type="date" id="mou-{{ $field }}" name="{{ $field }}" class="form-control" required
                                   value="{{ old($field, $agreement->{$field}?->toDateString()) }}">
                        </div>
                    @endforeach
                    <div class="form-group col-md-3">
                        <label for="mou-city">Kota Penandatanganan</label>
                        <input id="mou-city" name="city" class="form-control" maxlength="100" required value="{{ old('city', $agreement->city) }}">
                    </div>
                </div>
                @foreach(['scope' => ['Ruang Lingkup', 2400], 'payment_terms' => ['Ketentuan Pembayaran', 1600], 'additional_terms' => ['Ketentuan Tambahan (opsional)', 2400]] as $field => [$label, $limit])
                    <div class="form-group">
                        <label for="mou-{{ $field }}">{{ $label }}</label>
                        <textarea id="mou-{{ $field }}" name="{{ $field }}" class="form-control" rows="4" maxlength="{{ $limit }}"
                                  @required($field !== 'additional_terms')>{{ old($field, $agreement->{$field}) }}</textarea>
                        @if($field === 'scope' && !$agreement->exists)
                            <small class="form-text text-muted">Periksa ringkasan item invoice dan lengkapi rincian layanan yang disepakati.</small>
                        @endif
                    </div>
                @endforeach
                <div class="form-row">
                    @foreach(['provider' => 'Penyedia', 'client' => 'Client'] as $side => $label)
                        <div class="form-group col-md-6">
                            <label for="mou-{{ $side }}-signer">Nama Penandatangan {{ $label }}</label>
                            <input id="mou-{{ $side }}-signer" name="{{ $side }}_signer" class="form-control" maxlength="150" required
                                   value="{{ old($side.'_signer', $agreement->{$side.'_signer'}) }}">
                            <label for="mou-{{ $side }}-title" class="mt-2">Jabatan {{ $label }}</label>
                            <input id="mou-{{ $side }}-title" name="{{ $side }}_title" class="form-control" maxlength="150" required
                                   value="{{ old($side.'_title', $agreement->{$side.'_title'}) }}">
                        </div>
                    @endforeach
                </div>
                @unless($agreement->finalized_at)
                    <button type="submit" class="btn btn-primary">Simpan Draf</button>
                @endunless
            </fieldset>
        </form>
        @if($agreement->exists && !$agreement->finalized_at)
            <form class="mt-3" method="POST" action="{{ route('agreements.finalize', $agreement) }}"
                  onsubmit="return confirm('Finalkan draf terakhir yang sudah disimpan? Isinya akan terkunci.')">
                @csrf
                <p class="small text-muted mb-2">Simpan perubahan dan periksa PDF draf sebelum finalisasi.</p>
                <button type="submit" class="btn btn-outline-success">Finalkan MoU</button>
            </form>
        @endif
    </div>
    <div class="card-footer"><a href="{{ route($agreement->exists ? 'agreements.index' : 'invoices.index') }}" class="btn btn-outline-secondary">Kembali</a></div>
</div>
@endsection
