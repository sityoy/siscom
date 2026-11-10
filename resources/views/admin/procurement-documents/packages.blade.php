@extends('layouts.admin')

@section('page-title', $mode === 'third_party' ? 'SPJ Rekanan/Vendor' : ($mode === 'direct' ? 'SPJ Dikerjakan SIS.COM' : 'Semua Paket SPJ'))

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
            <h4 class="font-weight-bold mb-1">
                {{ $mode === 'third_party' ? 'Paket SPJ Rekanan/Vendor' : ($mode === 'direct' ? 'Paket SPJ Dikerjakan SIS.COM' : 'Pusat Paket SPJ') }}
            </h4>
            <p class="text-muted mb-0">Pilih jenis pelaksanaan terlebih dahulu, kemudian lengkapi delapan dokumen pendukung per project.</p>
        </div>
        <a href="{{ route('procurement-documents.create') }}" class="btn btn-primary mb-2">
            <i class="fas fa-plus mr-1"></i> Buat Dokumen Manual
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body py-2">
        <ul class="nav nav-pills">
            <li class="nav-item"><a class="nav-link {{ !$mode ? 'active' : '' }}" href="{{ route('procurement-documents.packages') }}">Semua Paket</a></li>
            <li class="nav-item"><a class="nav-link {{ $mode === 'direct' ? 'active' : '' }}" href="{{ route('procurement-documents.packages', ['mode' => 'direct']) }}">Dikerjakan SIS.COM</a></li>
            <li class="nav-item"><a class="nav-link {{ $mode === 'third_party' ? 'active' : '' }}" href="{{ route('procurement-documents.packages', ['mode' => 'third_party']) }}">Rekanan / Vendor</a></li>
        </ul>
    </div>
</div>

<div class="row">
    @forelse($projects as $project)
        @php
            $documentCount = $project->procurementDocuments->pluck('type')->unique()->count();
            $percentage = min(100, (int) round(($documentCount / $requiredDocumentCount) * 100));
            $invoice = $project->invoices->first();
            $package = $project->procurementPackage;
        @endphp

        <div class="col-xl-6 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="font-weight-bold mb-1">{{ $project->title }}</h5>
                        <span class="text-muted">{{ $project->client->name }}</span>
                        <div class="mt-2">
                            <span class="badge badge-{{ $package?->package_type === 'third_party' ? 'warning' : 'primary' }} p-2">
                                {{ $package?->type_label ?? 'SPJ Dikerjakan SIS.COM' }}
                            </span>
                        </div>
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

                    @if($package)
                        <div class="row border rounded mx-0 mb-3 py-2 bg-light">
                            <div class="col-4 text-center">
                                <small class="text-muted d-block">Nilai Kontrak</small>
                                <strong>Rp {{ number_format($package->contract_value, 0, ',', '.') }}</strong>
                            </div>
                            <div class="col-4 text-center border-left">
                                <small class="text-muted d-block">Biaya Vendor</small>
                                <strong>Rp {{ number_format($package->vendor_cost, 0, ',', '.') }}</strong>
                            </div>
                            <div class="col-4 text-center border-left">
                                <small class="text-muted d-block">Margin Bersih</small>
                                <strong class="{{ (float) $package->net_profit >= 0 ? 'text-success' : 'text-danger' }}">
                                    Rp {{ number_format($package->net_profit, 0, ',', '.') }}
                                </strong>
                                <small class="d-block text-muted">{{ number_format($package->actual_fee_percent, 2, ',', '.') }}%</small>
                            </div>
                        </div>

                        @if($package->package_type === 'third_party')
                            <div class="alert alert-warning py-2">
                                <strong>Vendor:</strong> {{ $package->vendor_name ?: 'Belum diisi' }}
                            </div>
                        @endif
                    @else
                        <div class="alert alert-secondary py-2">
                            Jenis paket dan pembagian biaya belum diatur. Default dianggap sebagai proyek langsung SIS.COM.
                        </div>
                    @endif

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
                        <a href="{{ route('procurement-documents.packages.edit', $project) }}"
                           class="btn btn-warning mr-2 mb-2">
                            <i class="fas fa-sliders-h mr-1"></i> Atur Jenis & Fee
                        </a>
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
