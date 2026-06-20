@extends('layouts.client')

@section('page-title', 'Client Dashboard')

@section('client-content')

<div class="row">

    {{-- TOTAL PROJECT --}}
    <div class="col-lg-3 col-md-6 mb-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <small class="text-muted">

                    Total Project

                </small>

                <h2 class="font-weight-bold text-primary mt-2">

                    {{ $projects }}

                </h2>

            </div>

        </div>

    </div>

    {{-- TOTAL INVOICE --}}
    <div class="col-lg-3 col-md-6 mb-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <small class="text-muted">

                    Total Invoice

                </small>

                <h2 class="font-weight-bold text-success mt-2">

                    {{ $invoices }}

                </h2>

            </div>

        </div>

    </div>

    {{-- TOTAL PAYMENT --}}
    <div class="col-lg-3 col-md-6 mb-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <small class="text-muted">

                    Total Pembayaran

                </small>

                <h2 class="font-weight-bold text-info mt-2">

                    {{ $payments ?? 0 }}

                </h2>

            </div>

        </div>

    </div>

    {{-- ACTIVE PROJECT --}}
    <div class="col-lg-3 col-md-6 mb-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <small class="text-muted">

                    Active Project

                </small>

                <h2 class="font-weight-bold text-warning mt-2">

                    {{ $activeProjects ?? 0 }}

                </h2>

            </div>

        </div>

    </div>


<div class="col-lg-3 col-md-6 mb-4">

    <div class="card border-0 shadow-sm h-100">

        <div class="card-body">

            <small class="text-muted">

                Open Ticket

            </small>

            <h2 class="font-weight-bold text-danger mt-2">

                {{ $openTickets }}

            </h2>

        </div>

    </div>

</div>

<div class="col-lg-3 col-md-6 mb-4">

    <div class="card border-0 shadow-sm h-100">

        <div class="card-body">

            <small class="text-muted">

                Notifikasi Baru

            </small>

            <h2 class="font-weight-bold text-primary mt-2">

                {{ $notifications }}

            </h2>

        </div>

    </div>

</div>
</div>

{{-- WELCOME --}}
<div class="card border-0 shadow-sm">

    <div class="card-body">

        <h4 class="font-weight-bold mb-3">

            Selamat Datang 👋

        </h4>

        <p class="text-muted mb-0">

            Portal client SIS.COM digunakan untuk
            melihat project, invoice, pembayaran,
            dan file project Anda secara realtime.

        </p>

    </div>

</div>

@endsection
