@extends('layouts.admin')

@section('page-title', 'Invoices')

@section('admin-content')
    @if(session('new_invoice_payment_id'))
        <div class="alert alert-info d-flex flex-wrap align-items-center justify-content-between">
            <span>Invoice baru siap menerima pembayaran.</span>
            <a class="btn btn-primary btn-sm" href="{{ route('payments.create', ['invoice_id' => session('new_invoice_payment_id')]) }}">
                Tambah Pembayaran
            </a>
        </div>
    @endif
    @include('invoices.list', ['adminView' => true])
@endsection
