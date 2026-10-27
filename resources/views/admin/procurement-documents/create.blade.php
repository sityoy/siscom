@extends('layouts.admin')

@section('page-title', 'Buat Dokumen Rekanan')

@section('admin-content')
<div class="card">
    <div class="card-header">
        <h4 class="mb-1 font-weight-bold">Dokumen Rekanan Baru</h4>
        <small class="text-muted">Data project dan client akan dimasukkan otomatis ke dokumen.</small>
    </div>
    <form method="POST" action="{{ route('procurement-documents.store') }}">
        @csrf
        <div class="card-body">@include('admin.procurement-documents._form')</div>
        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('procurement-documents.index') }}" class="btn btn-secondary">Kembali</a>
            <button class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan Dokumen</button>
        </div>
    </form>
</div>
@endsection
