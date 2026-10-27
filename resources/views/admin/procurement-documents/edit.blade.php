@extends('layouts.admin')

@section('page-title', 'Edit Dokumen Rekanan')

@section('admin-content')
<div class="card">
    <div class="card-header">
        <h4 class="mb-1 font-weight-bold">{{ $document->type_label }}</h4>
        <small class="text-muted">{{ $document->document_number }}</small>
    </div>
    <form method="POST" action="{{ route('procurement-documents.update', $document) }}">
        @csrf
        @method('PUT')
        <div class="card-body">@include('admin.procurement-documents._form')</div>
        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('procurement-documents.show', $document) }}" class="btn btn-secondary">Kembali</a>
            <button class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
