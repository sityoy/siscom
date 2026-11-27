@extends('layouts.admin')

@section('page-title', 'Edit Pengeluaran Modal')

@section('admin-content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('capital.update', $contribution) }}">
            @csrf
            @method('PUT')
            @include('admin.capital._fields')
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="{{ route('capital.index') }}" class="btn btn-outline-secondary">Kembali</a>
        </form>
    </div>
</div>
@endsection
