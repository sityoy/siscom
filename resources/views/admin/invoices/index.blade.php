@extends('layouts.admin')

@section('page-title', 'Invoices')

@section('admin-content')
    @include('invoices.list', ['adminView' => true])
@endsection
