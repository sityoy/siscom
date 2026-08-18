@extends('adminlte::page')

@section(
    'title',
    'SIS.COM | ' .
    (auth()->user()->name ?? 'Client')
)

@php

$notificationCount = auth()->check()
    && auth()->user()->client

    ? auth()->user()
        ->client
        ->notifications()
        ->where('is_read', false)
        ->count()

    : 0;

@endphp

{{-- HEADER --}}
@section('content_header')

<div class="d-flex justify-content-between align-items-center mb-3">

    <div>

        <h1 class="m-0 font-weight-bold text-primary">

            @yield('page-title')

        </h1>

        <small class="text-muted">

            SIS.COM Client Portal

        </small>
        <a
            href="{{ route('client.notifications') }}"
            class="btn btn-light position-relative">

            <i class="fas fa-bell"></i>

            @if($clientNotificationCount > 0)

                <span
                    class="badge badge-danger position-absolute"
                    style="
                        top:-5px;
                        right:-5px;
                    ">

                    {{ $clientNotificationCount }}

                </span>

            @endif

        </a>

    </div>


    <div class="text-right">

        <small class="text-muted d-block">

            {{ now()->format('d M Y') }}

        </small>

        <strong>

            {{ auth()->user()->name ?? 'Client' }}

        </strong>

    </div>

</div>


@stop

{{-- CONTENT --}}
@section('content')

<div class="client-wrapper">

    @yield('client-content')

</div>


@stop

{{-- CSS --}}
@section('css')

<style>

    body {

        background: #f4f7fb !important;

        font-family: 'Segoe UI', sans-serif;

    }

    /* NAVBAR */

    .main-header.navbar {

        background: white !important;

        border-bottom: 1px solid #e5e7eb;

    }

    /* SIDEBAR */

    .main-sidebar {

        background: linear-gradient(
            180deg,
            #0a3b96 0%,
            #1e293b 100%
        ) !important;

    }

    .brand-link {

        border-bottom:
            1px solid rgba(255,255,255,.08);

    }

    .brand-text {

        color: white !important;

        font-weight: bold;

    }

    /* SIDEBAR MENU */

    .nav-sidebar .nav-link {

        border-radius: 12px;

        margin: 6px 12px;

        padding: 12px 16px;

        color: rgba(255,255,255,.85) !important;

    }

    .nav-sidebar .nav-link:hover {

        background:
            rgba(255,255,255,.08);

        color: white !important;

    }

    .nav-sidebar .nav-link.active {

        background: white !important;

        color: #0f172a !important;

        font-weight: bold;

    }

    /* CONTENT */

    .content-wrapper {

        background: #f4f7fb !important;

    }

    /* CARD */

    .card {

        border: none !important;

        border-radius: 18px !important;

        box-shadow:
            0 4px 20px rgba(15,23,42,0.05) !important;

        overflow: hidden;

    }

    .card-header {

        background: white !important;

        border-bottom: 1px solid #f1f5f9;

        padding: 16px 20px;

    }

    .card-body {

        padding: 20px;

    }

    /* TABLE */

    .table thead th {

        background: #f8fafc;

        border-bottom: 2px solid #e5e7eb;

        color: #334155;

        font-size: 13px;

    }

    .table td {

        vertical-align: middle !important;

    }

    /* BUTTON */

    .btn {

        border-radius: 10px !important;

        font-weight: 600;

    }

    .btn-primary {

        background: #2563eb !important;

        border-color: #2563eb !important;

    }

    .btn-primary:hover {

        background: #334cbd !important;

    }

    /* BADGE */

    .badge {

        border-radius: 30px !important;

        padding: 6px 10px;

        font-size: 11px;

    }

    /* FORM */

    .form-control {

        border-radius: 10px !important;

        border: 1px solid #dbe2ea;

        box-shadow: none !important;

    }

    .form-control:focus {

        border-color: #0f172a;

    }

</style>

@stop
