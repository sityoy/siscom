@extends('layouts.admin')

@section('page-title', 'Tambah Invoice')

@section('admin-content')
    @include('admin.invoices._form', ['invoice' => null])
@endsection
