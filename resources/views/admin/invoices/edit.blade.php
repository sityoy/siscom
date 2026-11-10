@extends('layouts.admin')

@section('page-title', 'Edit Invoice')

@section('admin-content')
    @include('admin.invoices._form', ['invoiceDefaults' => []])
@endsection
