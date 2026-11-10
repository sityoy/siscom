@extends('layouts.admin')

@section('page-title', 'Paket SPJ per Project')

@section('admin-content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card mb-4">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
        <div class="mb-2">
            <h4 class="font-weight-bold mb-1">Pusat Paket SPJ Rekanan</h4>
            <p class="text-muted mb-0">Satu project memiliki delapan dokumen pendukung yang dapat dibuat, diperiksa, dan dicetak sebagai satu paket.</p>
        </div>
        <a href="{{ route('procurement-documents.create') }}" class="btn btn-primary mb-2">
            <i class="fas fa-plus mr-1"></i> Buat Dokumen Manual
        </a>
    </div>
</div>

<div class="row">
    @forelse($projects as $project)
        @php
            $documentCount = $project->procurementDocuments->pluck('type')->unique()->count();
            $percentage = min(100, (int) round(($documentCount / $requiredDocumentCount) * 100));
            $invoice = $project->invoices->first();
        @endphp

        <div class="col-xl-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="font-weight-bold mb-1">{{ $project->title }}</h5>
                        <span class="text-muted">{{ $project->client->name }}</span>
                    </div>
                    <span class="badge badge-{{ $documentCount === $requiredDocumentCount ? 'success' : 'warning' }} p-2">
                        {{ $documentCount }}/{{ $requiredDocumentCount }} Dokumen
                    </span>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Invoice Acuan</small>
                            <strong>{{ $invoice?->invoice_number ?? '-' }}</strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Tanggal Paket</small>
                            <strong>{{ $invoice?->invoice_date?->format('d M Y') ?? '-' }}</strong>
                        </div>
                    </div>

                    <div class="progress mb-3" style="height: 10px;">
                        <div class="progress-bar bg-{{ $percentage === 100 ? 'success' : 'primary' }}"
                             role="progressbar" style="width: {{ $percentage }}%;"></div>
                    </div>

                    <div class="mb-3">
                        @foreach(\App\Models\ProcurementDocument::TYPES as $type => $label)
                            @php($available = $project->procurementDocuments->contains('type', $type))
                            <span class="badge badge-{{ $available ? 'success' : 'light' }} border mr-1 mb-1 p-2">
                                <i class="fas fa-{{ $available ? 'check' : 'minus' }} mr-1"></i>{{ $label }}
                            </span>
                        @endforeach
                    </div>

                    <div class="d-flex flex-wrap">
                        <a href="{{ route('procurement-documents.index', ['project_id' => $project->id]) }}"
                           class="btn btn-outline-info mr-2 mb-2">
                            <i class="fas fa-folder-open mr-1"></i> Buka Dokumen
                        </a>

                        <form method="POST" action="{{ route('procurement-documents.generate', $project) }}" class="mr-2 mb-2"
                              onsubmit="return confirm('Buat seluruh dokumen SPJ yang masih belum tersedia?')">
                            @csrf
                            <button class="btn btn-outline-primary">
                                <i class="fas fa-magic mr-1"></i> {{ $documentCount ? 'Lengkapi Paket' : 'Buat Paket' }}
                            </button>
                        </form>

                        @if($documentCount > 0)
                            <a href="{{ route('procurement-documents.package', $project) }}"
                               target="_blank" class="btn btn-danger mb-2">
                                <i class="fas fa-file-pdf mr-1"></i> Cetak Paket PDF
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card"><div class="card-body text-center text-muted py-5">Belum ada project yang memiliki invoice aktif.</div></div>
        </div>
    @endforelse
</div>

{{ $projects->links() }}

@endsection
