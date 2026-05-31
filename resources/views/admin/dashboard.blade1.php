@extends('layouts.admin')

@section('page-title', 'Dashboard')

@section('admin-content')


{{-- TOP STATS --}}
<div class="row">

    {{-- TOTAL PEMASUKAN --}}
    <div class="col-lg-3 col-md-6 mb-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <small class="text-muted">

                    Jumlah Pemasukan

                </small>

                <h3 class="font-weight-bold text-success mt-2">

                    Rp {{ number_format($totalIncome,0,',','.') }}

                </h3>

            </div>

        </div>

    </div>

    {{-- BULAN INI --}}
    <div class="col-lg-3 col-md-6 mb-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <small class="text-muted">

                    Pemasukan Bulan Ini

                </small>

                <h3 class="font-weight-bold text-primary mt-2">

                    Rp {{ number_format($currentMonthIncome,0,',','.') }}

                </h3>

            </div>

        </div>

    </div>

    {{-- CLIENT --}}
    <div class="col-lg-3 col-md-6 mb-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <small class="text-muted">

                    Jumlah Klien

                </small>

                <h3 class="font-weight-bold text-info mt-2">

                    {{ $totalClients }}

                </h3>

            </div>

        </div>

    </div>

    {{-- USERS --}}
    <div class="col-lg-3 col-md-6 mb-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <small class="text-muted">

                    Jumlah Pengguna

                </small>

                <h3 class="font-weight-bold text-dark mt-2">

                    {{ $totalUsers }}

                </h3>

            </div>

        </div>

    </div>

</div>

{{-- SECOND ROW --}}
<div class="row">

    {{-- PROJECT --}}
    <div class="col-lg-3 col-md-6 mb-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <small class="text-muted">

                    Jumlah Proyek

                </small>

                <h3 class="font-weight-bold text-warning mt-2">

                    {{ $totalProjects }}

                </h3>

            </div>

        </div>

    </div>

    {{-- COMPLETED --}}
    <div class="col-lg-3 col-md-6 mb-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <small class="text-muted">

                    Proyek Selesai

                </small>

                <h3 class="font-weight-bold text-success mt-2">

                    {{ $completedProjects }}

                </h3>

            </div>

        </div>

    </div>

    {{-- PENDING --}}
    <div class="col-lg-3 col-md-6 mb-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <small class="text-muted">

                    Proyek Tertunda

                </small>

                <h3 class="font-weight-bold text-danger mt-2">

                    {{ $pendingProjects }}

                </h3>

            </div>

        </div>

    </div>

    {{-- PAID --}}
    <div class="col-lg-3 col-md-6 mb-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <small class="text-muted">

                    Faktur Terbayar

                </small>

                <h3 class="font-weight-bold text-primary mt-2">

                    {{ $paidInvoices }}

                </h3>

            </div>

        </div>

    </div>

</div>

{{-- THIRD ROW --}}
<div class="row">

    {{-- UNPAID --}}
    <div class="col-lg-3 col-md-6 mb-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <small class="text-muted">

                    Tagihan Belum Dibayar

                </small>

                <h3 class="font-weight-bold text-danger mt-2">

                    {{ $unpaidInvoices }}

                </h3>

            </div>

        </div>

    </div>

</div>

<div class="col-lg-3 col-md-6 mb-4">

    <div class="card border-0 shadow-sm h-100">

        <div class="card-body">

            <small class="text-muted">

                Open Ticket

            </small>

            <h3 class="font-weight-bold text-warning mt-2">

                {{ $openTickets }}

            </h3>

        </div>

    </div>

</div>

{{-- ANALYTICS ROW --}}
<div class="row">

    {{-- TOTAL PAYMENT --}}
    <div class="col-lg-3 col-md-6 mb-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <small class="text-muted">

                    Jumlah Pembayaran

                </small>

                <h3 class="font-weight-bold text-info mt-2">

                    {{ $totalPayments }}

                </h3>

            </div>

        </div>

    </div>

    {{-- PENDING REVENUE --}}
    <div class="col-lg-3 col-md-6 mb-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <small class="text-muted">

                    Pendapatan Tertunda

                </small>

                <h3 class="font-weight-bold text-danger mt-2">

                    Rp {{ number_format($pendingRevenue,0,',','.') }}

                </h3>

            </div>

        </div>

    </div>

    {{-- CASHBACK --}}
    <div class="col-lg-3 col-md-6 mb-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <small class="text-muted">

                    Jumlah Cashback

                </small>

                <h3 class="font-weight-bold text-success mt-2">

                    {{ $totalCashback }}%

                </h3>

            </div>

        </div>

    </div>

    {{-- PROJECT RATE --}}
    <div class="col-lg-3 col-md-6 mb-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <small class="text-muted">

                    Penyelesaian Proyek

                </small>

                <h3 class="font-weight-bold text-primary mt-2">

                    {{ $projectCompletionRate }}%

                </h3>

                <div class="progress mt-3"
                     style="height:8px;">

                    <div class="progress-bar bg-primary"
                         style="
                            width:
                            {{ $projectCompletionRate }}%;
                         ">

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

{{-- LATEST DATA --}}

    {{-- LATEST INVOICES --}}
<div class="row">
    <div class="col-lg-6 mb-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-header bg-white border-0">

                <h5 class="mb-0 font-weight-bold">

                    Invoice Terbaru

                </h5>

            </div>

            <div class="card-body p-0">

                <table class="table table-hover mb-0">

                    <thead>

                        <tr>

                            <th>Invoice</th>
                            <th>Client</th>
                            <th>Status</th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach($latestInvoices as $invoice)

                            <tr>

                                <td>

                                    {{ $invoice->invoice_number }}

                                </td>

                                <td>

                                    {{ $invoice->client->name ?? '-' }}

                                </td>

                                <td>

                                    @if($invoice->status == 'paid')

                                        <span class="badge bg-success">

                                            Paid

                                        </span>

                                    @elseif($invoice->status == 'partial')

                                        <span class="badge bg-warning">

                                            Partial

                                        </span>

                                    @else

                                        <span class="badge bg-danger">

                                            Unpaid

                                        </span>

                                    @endif

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    {{-- LATEST PAYMENTS --}}
    <div class="col-lg-6 mb-4">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-header bg-white border-0">

                <h5 class="mb-0 font-weight-bold">

                    Pembayaran Terbaru

                </h5>

            </div>

            <div class="card-body p-0">

                <table class="table table-hover mb-0">

                    <thead>

                        <tr>

                            <th>Invoice</th>
                            <th>Nominal</th>
                            <th>Tanggal</th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach($latestPayments as $payment)

                            <tr>

                                <td>

                                    {{ $payment->invoice->invoice_number ?? '-' }}

                                </td>

                                <td class="text-success font-weight-bold">

                                    Rp {{ number_format($payment->amount,0,',','.') }}

                                </td>

                                <td>

                                    {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    <div class="col-lg-6 mb-4">

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white">

                <h5 class="mb-0">

                    Top Client

                </h5>

            </div>

            <div class="card-body p-0">

                <table class="table mb-0">

                    <thead>

                        <tr>

                            <th>Client</th>
                            <th>Total Project</th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach($topClients as $client)

                        <tr>

                            <td>

                                {{ $client->name }}

                            </td>

                            <td>

                                {{ $client->projects_count }}

                            </td>

                        </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        </div>

</div>

<div class="col-lg-6 mb-4">

    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white">

            <h5 class="mb-0">

                Recent Tickets

            </h5>

        </div>

        <div class="card-body p-0">

            <table class="table mb-0">

                <thead>

                    <tr>

                        <th>Client</th>
                        <th>Subject</th>
                        <th>Status</th>

                    </tr>

                </thead>

                <tbody>

                    @foreach($latestTickets as $ticket)

                    <tr>

                        <td>

                            {{ $ticket->client->name ?? '-' }}

                        </td>

                        <td>

                            {{ $ticket->subject }}

                        </td>

                        <td>

                            @if($ticket->status == 'closed')

                                <span class="badge badge-success">

                                    Closed

                                </span>

                            @else

                                <span class="badge badge-warning">

                                    Open

                                </span>

                            @endif

                        </td>

                    </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    </div>

</div>

</div>
</div>



{{-- CHART --}}
<div class="card border-0 shadow-sm mt-2">

    <div class="card-header bg-white border-0">

        <h5 class="mb-0 font-weight-bold">

            Statistik Pendapatan

        </h5>

    </div>

    <div class="card-body">

        <canvas id="dashboardChart"
                height="100"></canvas>

    </div>

</div>
</div>

@endsection

@section('js')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const ctx =
    document.getElementById('dashboardChart');

new Chart(ctx, {

    type: 'bar',

    data: {

        labels: [

            @foreach($monthlyPayments as $payment)

                'Bulan {{ $payment->month }}',

            @endforeach

        ],

        datasets: [{

            label: 'Pendapatan',

            data: [

                @foreach($monthlyPayments as $payment)

                    {{ $payment->total }},

                @endforeach

            ],

            borderWidth: 1,

            borderRadius: 10,

        }]

    },

    options: {

        responsive: true,

        plugins: {

            legend: {

                display: false

            }

        },

        scales: {

            y: {

                beginAtZero: true

            }

        }

    }

});

</script>

@stop
