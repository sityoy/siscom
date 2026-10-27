@extends('layouts.admin')

@section('page-title', 'Detail Dokumen Rekanan')

@section('admin-content')
@php
    $statusClasses = [
        'draft' => 'secondary',
        'awaiting_signature' => 'warning',
        'signed' => 'success',
        'paid' => 'primary',
        'archived' => 'dark',
    ];
@endphp

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1 font-weight-bold">{{ $document->type_label }}</h4>
                    <small class="text-muted">{{ $document->document_number }}</small>
                </div>
                <span class="badge badge-{{ $statusClasses[$document->status] ?? 'secondary' }} p-2">
                    {{ $document->status_label }}
                </span>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Client/Sekolah</dt>
                    <dd class="col-sm-8">{{ $document->project->client->name }}</dd>

                    <dt class="col-sm-4">Project</dt>
                    <dd class="col-sm-8">{{ $document->project->title }}</dd>

                    <dt class="col-sm-4">Tanggal</dt>
                    <dd class="col-sm-8">{{ $document->document_date->format('d M Y') }}</dd>

                    <dt class="col-sm-4">Perihal</dt>
                    <dd class="col-sm-8">{{ $document->subject ?: '-' }}</dd>

                    <dt class="col-sm-4">Nilai Pekerjaan</dt>
                    <dd class="col-sm-8 font-weight-bold text-success">Rp {{ number_format($document->amount, 0, ',', '.') }}</dd>

                    <dt class="col-sm-4">Invoice</dt>
                    <dd class="col-sm-8">{{ $document->invoice?->invoice_number ?? '-' }}</dd>

                    <dt class="col-sm-4">Sumber Dana</dt>
                    <dd class="col-sm-8">{{ $document->funding_source ?: '-' }}</dd>

                    <dt class="col-sm-4">Metode Transaksi</dt>
                    <dd class="col-sm-8">
                        {{ $document->transaction_method
                            ? ($document->transaction_method === 'siplah' ? 'SIPLah' : 'Non-SIPLah/Langsung')
                            : '-' }}
                    </dd>

                    <dt class="col-sm-4">Penandatangan Sekolah</dt>
                    <dd class="col-sm-8">
                        {{ $document->school_signer_name ?: '-' }}
                        @if($document->school_signer_title)<small class="d-block text-muted">{{ $document->school_signer_title }}</small>@endif
                    </dd>

                    <dt class="col-sm-4">Penandatangan SIS.COM</dt>
                    <dd class="col-sm-8">
                        {{ $document->provider_signer_name ?: '-' }}
                        @if($document->provider_signer_title)<small class="d-block text-muted">{{ $document->provider_signer_title }}</small>@endif
                    </dd>
                </dl>

                @if($document->terms)
                    <hr>
                    <h6 class="font-weight-bold">Ketentuan</h6>
                    <div class="text-muted">{!! nl2br(e($document->terms)) !!}</div>
                @endif

                @if($document->notes)
                    <hr>
                    <h6 class="font-weight-bold">Catatan</h6>
                    <div class="text-muted">{!! nl2br(e($document->notes)) !!}</div>
                @endif
            </div>
            <div class="card-footer d-flex flex-wrap justify-content-between">
                <a href="{{ route('procurement-documents.index', ['project_id' => $document->project_id]) }}" class="btn btn-secondary mb-2">Kembali</a>
                <div>
                    <a href="{{ route('procurement-documents.pdf', $document) }}" target="_blank" class="btn btn-danger mb-2">
                        <i class="fas fa-file-pdf mr-1"></i> Lihat PDF
                    </a>
                    <a href="{{ route('procurement-documents.edit', $document) }}" class="btn btn-warning mb-2">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h5 class="mb-0 font-weight-bold">Dokumen Ditandatangani</h5></div>
            <div class="card-body">
                @if($document->signed_file_path)
                    <div class="alert alert-success">
                        Berkas yang telah ditandatangani sudah tersimpan.
                    </div>
                    <a href="{{ route('procurement-documents.signed.download', $document) }}" class="btn btn-success btn-block mb-3">
                        <i class="fas fa-download mr-1"></i> Unduh Berkas
                    </a>
                @endif

                <form method="POST" enctype="multipart/form-data"
                      action="{{ route('procurement-documents.signed.upload', $document) }}">
                    @csrf
                    <div class="form-group">
                        <label>{{ $document->signed_file_path ? 'Ganti berkas' : 'Unggah setelah ditandatangani' }}</label>
                        <input type="file" name="signed_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                        <small class="text-muted">PDF/JPG/PNG, maksimal 10 MB.</small>
                    </div>
                    <button class="btn btn-primary btn-block"><i class="fas fa-upload mr-1"></i> Unggah</button>
                </form>
            </div>
        </div>

        <div class="card border-danger">
            <div class="card-body">
                <form method="POST" action="{{ route('procurement-documents.destroy', $document) }}"
                      onsubmit="return confirm('Yakin menghapus dokumen ini?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger btn-block"><i class="fas fa-trash mr-1"></i> Hapus Dokumen</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
