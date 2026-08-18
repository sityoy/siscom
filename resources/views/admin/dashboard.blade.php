@extends('layouts.admin')

@section('page-title', 'Dashboard')

@section('admin-content')

<div class="mb-4">

    <h3 class="mb-1">

        Welcome Back,
        {{ auth()->user()->name }}

    </h3>
    @if($graceClients > 0)

<div class="alert alert-warning mt-3">

    <strong>
        ⚠ {{ $graceClients }}
        Client Dalam Masa Tenggang
    </strong>

    <a href="{{ route('clients.index') }}"
       class="float-end">

        Lihat

    </a>

</div>

@endif

@if($expiredClients > 0)

<div class="alert alert-danger">

    <strong>
        ❌ {{ $expiredClients }}
        Client Expired
    </strong>

    <a href="{{ route('clients.index') }}"
       class="float-end text-white">

        Lihat

    </a>

</div>

@endif

    <small class="text-muted">

        {{ now()->format('l, d F Y H:i') }}

    </small>

</div>


@if($expiringSoonClients > 0)

<div class="alert alert-warning">

    <strong>
        ⚠️ Subscription Alert!
    </strong>

    There are

    <strong>
        {{ $expiringSoonClients }}
    </strong>

    client subscriptions expiring within the next 7 days.

</div>

@endif


@if($expiredClients > 0)

<div class="alert alert-danger">

    <strong>
        🚨 Subscription Expired!
    </strong>

    There are

    <strong>
        {{ $expiredClients }}
    </strong>

    clients whose subscription grace period has ended.

</div>

@endif

{{-- BARIS 1 --}}
<div class="row">

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small>Total Revenue</small>
                <h2 class="text-success">
                    Rp {{ number_format($totalIncome,0,',','.') }}
                </h2>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small>Monthly Revenue</small>
                <h2 class="text-primary">
                    Rp {{ number_format($currentMonthIncome,0,',','.') }}
                </h2>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small>Pending Revenue</small>
                <h2 class="text-danger">
                    Rp {{ number_format($pendingRevenue,0,',','.') }}
                </h2>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small>Total Clients</small>
                <h2 class="text-info">
                    {{ $totalClients }}
                </h2>
            </div>
        </div>
    </div>

</div>

{{-- BARIS 2 --}}
<div class="row">

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small>Active Clients</small>
                <h2 class="text-success">
                    {{ $activeClients }}
                </h2>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small>Grace Period</small>
                <h2 class="text-warning">
                    {{ $graceClients }}
                </h2>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small>Expired</small>
                <h2 class="text-danger">
                    {{ $expiredClients }}
                </h2>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small>Expiring Soon</small>
                <h2 class="text-primary">
                    {{ $expiringSoonClients }}
                </h2>
            </div>
        </div>
    </div>

</div>

{{-- BARIS 3 --}}
<div class="row">

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small>Paid Invoices</small>
                <h2 class="text-success">
                    {{ $paidInvoices }}
                </h2>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small>Unpaid Invoices</small>
                <h2 class="text-danger">
                    {{ $unpaidInvoices }}
                </h2>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small>Overdue Invoices</small>
                <h2 class="text-warning">
                    {{ $overdueInvoices }}
                </h2>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <small>Total Projects</small>
                <h2 class="text-info">
                    {{ $totalProjects }}
                </h2>
            </div>
        </div>
    </div>

</div>



    <div class="row">

    {{-- Invoice Terbaru --}}

    
    <div class="col-lg-6 mb-4">

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white">
                <h4 class="mb-0">Latest Invoices</h4>
            </div>
            

            <div class="table-responsive">

                <table class="table mb-0">

                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Client</th>
                            <th>Status</th>
                            <th>Total</th>
                        </tr>
                    </thead>

                    <tbody>

                    @forelse($latestInvoices as $invoice)

                        <tr>
                            <td>{{ $invoice->invoice_number }}</td>
                            <td>{{ $invoice->client->name ?? '-' }}</td>
                            <td>{{ ucfirst($invoice->status) }}</td>
                            <td>Rp {{ number_format($invoice->grand_total,0,',','.') }}</td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                No invoices found
                            </td>
                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    {{-- Pembayaran Terbaru --}}
    <div class="col-lg-6 mb-4">

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white">
                <h4 class="mb-0">Latest Payments</h4>
            </div>

            <div class="table-responsive">

                <table class="table mb-0">

                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Amount</th>
                            <th>Date</th>
                        </tr>
                    </thead>

                    <tbody>

                    @forelse($latestPayments as $payment)

                        <tr>
                            <td>{{ $payment->invoice->invoice_number ?? '-' }}</td>
                            <td>Rp {{ number_format($payment->amount,0,',','.') }}</td>
                            <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="3" class="text-center text-muted">
                                No payments found
                            </td>
                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    <div class="col-lg-6 mb-4">

        <div class="card border-0 shadow-sm">

            <div class="card-header bg-white">

                <h5 class="mb-0">

                    Top Clients

                </h5>

            </div>

            <div class="card-body p-0">

                <table class="table mb-0">

                    <thead>

                        <tr>

                            <th>Client</th>
                            <th>Projects</th>
                            <th>Revenue</th>


                        </tr>

                    </thead>

                    <tbody>

                        @forelse($topClients as $client)

                            <tr>

                                <td>
                                    {{ $client->name }}
                                </td>

                                <td>
                                    {{ $client->projects_count }}
                                </td>

                                <td>
                                    Rp {{ number_format(
                                        $client->invoices_sum_grand_total,
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="3"
                                    class="text-center text-muted">

                                    No clients found

                                </td>

                            </tr>

                        @endforelse

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

                        @forelse($latestTickets as $ticket)

                            <tr>

                                <td>{{ $ticket->client->name ?? '-' }}</td>

                                <td>{{ $ticket->subject }}</td>

                                <td>

                                    @if($ticket->status == 'closed')

                                        <span class="badge badge-success">
                                            Closed
                                        </span>

                                    @elseif($ticket->status == 'progress')

                                        <span class="badge badge-primary">
                                            Progress
                                        </span>

                                    @else

                                        <span class="badge badge-warning">
                                            Open
                                        </span>

                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="3"
                                    class="text-center text-muted">

                                    No tickets found

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">

    <div class="card-header bg-white">

        <h4 class="mb-0">

            Revenue Statistics

        </h4>

    </div>

    <div class="card-body">

        <canvas id="incomeChart"></canvas>

    </div>

</div>



@endsection

@section('js')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

const ctx =
    document.getElementById('incomeChart');

new Chart(ctx, {

    type: 'bar',

    data: {

        labels: [

            @foreach($monthlyPayments as $payment)

                '{{ $payment->month }}',

            @endforeach

        ],

        datasets: [{

            label: 'Revenue',

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
