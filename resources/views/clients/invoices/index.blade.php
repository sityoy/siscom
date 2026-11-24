@extends('layouts.client')

@section('page-title', 'Invoice Saya')

@section('client-content')
    @include('invoices.list', ['adminView' => false])
@endsection
