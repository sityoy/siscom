@extends('layouts.client')

@section('page-title', 'Dokumen Rekanan')

@section('client-content')
<div class="card">
    <div class="card-header">
        <h4 class="mb-1 font-weight-bold">Dokumen Project dan SPJ</h4>
        <small class="text-muted">Unduh dokumen yang telah disiapkan SIS.COM untuk sekolah.</small>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nomor/Tanggal</th>
                        <th>Dokumen</th>
                        <th>Project</th>
                        <th>Status</th>
                        <th class="text-center">Unduh</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $document)
                        <tr>
                            <td>
                                <strong>{{ $document->document_number }}</strong>
                                <small class="d-block text-muted">{{ $document->document_date->format('d M Y') }}</small>
                            </td>
                            <td>{{ $document->type_label }}</td>
                            <td>{{ $document->project->title }}</td>
                            <td><span class="badge badge-info">{{ $document->status_label }}</span></td>
                            <td class="text-center text-nowrap">
                                <a href="{{ route('client.procurement-documents.pdf', $document) }}" target="_blank" class="btn btn-danger btn-sm">PDF</a>
                                @if($document->signed_file_path)
                                    <a href="{{ route('client.procurement-documents.signed', $document) }}" class="btn btn-success btn-sm">Bertanda Tangan</a>
                                @endif
                                @if(in_array($document->status, ['ready', 'awaiting_signature', 'signed'], true))
                                    <form method="POST" enctype="multipart/form-data"
                                          action="{{ route('client.procurement-documents.signed.upload', $document) }}"
                                          class="mt-2">
                                        @csrf
                                        <input type="file" name="signed_file" accept=".pdf,.jpg,.jpeg,.png"
                                               class="form-control form-control-sm mb-1" required>
                                        <button class="btn btn-primary btn-sm btn-block">Kirim Hasil TTD</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-5">Belum ada dokumen yang dipublikasikan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $documents->links() }}
    </div>
</div>
@endsection
