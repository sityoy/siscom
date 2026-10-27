@extends('layouts.admin')

@section('page-title', 'Dokumen Rekanan & SPJ')

@section('admin-content')

<div class="row">
    <div class="col-md-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner"><h3>{{ $summary['total'] }}</h3><p>Total Dokumen</p></div>
            <div class="icon"><i class="fas fa-file-contract"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner"><h3>{{ $summary['awaiting'] }}</h3><p>Menunggu Tanda Tangan</p></div>
            <div class="icon"><i class="fas fa-pen-nib"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ $summary['signed'] }}</h3><p>Ditandatangani/Selesai</p></div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="small-box bg-info">
            <div class="inner"><h3>{{ $summary['projects'] }}</h3><p>Project Berdokumen</p></div>
            <div class="icon"><i class="fas fa-project-diagram"></i></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1 font-weight-bold">Administrasi Rekanan Sekolah</h4>
            <small class="text-muted">Kelola surat, BAST, kwitansi, perpajakan, dan paket SPJ per project.</small>
        </div>
        <a href="{{ route('procurement-documents.create', request()->only('project_id')) }}"
           class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Buat Dokumen
        </a>
    </div>

    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="GET" class="row mb-4">
            <div class="col-md-5 mb-2">
                <label>Project</label>
                <select name="project_id" class="form-control">
                    <option value="">Semua project</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" @selected((string) request('project_id') === (string) $project->id)>
                            {{ $project->client->name }} — {{ $project->title }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <label>Jenis Dokumen</label>
                <select name="type" class="form-control">
                    <option value="">Semua jenis</option>
                    @foreach(\App\Models\ProcurementDocument::TYPES as $value => $label)
                        <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="">Semua status</option>
                    @foreach(\App\Models\ProcurementDocument::STATUSES as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2 d-flex align-items-end">
                <button class="btn btn-outline-primary btn-block">Filter</button>
            </div>
        </form>

        @if(request('project_id'))
            <div class="mb-3 d-flex flex-wrap">
                <form method="POST" action="{{ route('procurement-documents.generate', request('project_id')) }}" class="mr-2 mb-2"
                      onsubmit="return confirm('Buat seluruh draf dokumen yang belum tersedia untuk project ini?')">
                    @csrf
                    <button class="btn btn-outline-primary">
                        <i class="fas fa-magic mr-1"></i> Buat Draf Paket Otomatis
                    </button>
                </form>
                <a href="{{ route('procurement-documents.package', request('project_id')) }}"
                   class="btn btn-outline-danger mb-2" target="_blank">
                    <i class="fas fa-file-pdf mr-1"></i> Cetak Dokumen Pendukung SPJ
                </a>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nomor/Tanggal</th>
                        <th>Jenis Dokumen</th>
                        <th>Client & Project</th>
                        <th>Nilai</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $document)
                        @php
                            $statusClasses = [
                                'draft' => 'secondary',
                                'awaiting_signature' => 'warning',
                                'signed' => 'success',
                                'paid' => 'primary',
                                'archived' => 'dark',
                            ];
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $document->document_number }}</strong>
                                <small class="d-block text-muted">{{ $document->document_date->format('d M Y') }}</small>
                            </td>
                            <td>{{ $document->type_label }}</td>
                            <td>
                                <strong>{{ $document->project->client->name }}</strong>
                                <small class="d-block text-muted">{{ $document->project->title }}</small>
                            </td>
                            <td>Rp {{ number_format($document->amount, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge badge-{{ $statusClasses[$document->status] ?? 'secondary' }}">
                                    {{ $document->status_label }}
                                </span>
                                @if($document->signed_file_path)
                                    <small class="d-block text-success mt-1"><i class="fas fa-paperclip"></i> Berkas terunggah</small>
                                @endif
                            </td>
                            <td class="text-center text-nowrap">
                                <a href="{{ route('procurement-documents.show', $document) }}" class="btn btn-info btn-sm">Detail</a>
                                <a href="{{ route('procurement-documents.pdf', $document) }}" class="btn btn-danger btn-sm" target="_blank">PDF</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">Belum ada dokumen rekanan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $documents->links() }}
    </div>
</div>

@endsection
