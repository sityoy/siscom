@extends('layouts.admin')

@section('page-title', 'Dashboard')

@section('admin-content')

<div class="mb-4">

    <h3 class="mb-1">

        Welcome Back,
        {{ auth()->user()->name }}

    </h3>

    <small class="text-muted">

        {{ now()->format('l, d F Y H:i') }}

    </small>

</div>

{{-- BARIS 1 --}}
<div class="row">

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted">Total Revenue</small>
                <h2 class="font-weight-bold text-success mt-2">
                    Rp {{ number_format($totalIncome,0,',','.') }}
                </h2>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted">Monthly Revenue</small>
                <h2 class="font-weight-bold text-primary mt-2">
                    Rp {{ number_format($currentMonthIncome,0,',','.') }}
                </h2>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted">Pending Revenue</small>
                <h2 class="font-weight-bold text-danger mt-2">
                    Rp {{ number_format($pendingRevenue,0,',','.') }}
                </h2>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted">Clients Cashback Rewards</small>
                    <h2 class="font-weight-bold text-success mt-2">
                        Rp {{ number_format($totalCashback,0,',','.') }}
                    </h2>
                </div>
            </div>
        </div>
</div>

{{-- BARIS 2 --}}
<div class="row">

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted">Total Users</small>
                <h2 class="font-weight-bold text-dark mt-2">
                    {{ $totalUsers }}
                </h2>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted">Total Clients</small>
                <h2 class="font-weight-bold text-info mt-2">
                    {{ $totalClients }}
                </h2>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted">Total Projects</small>
                <h2 class="font-weight-bold text-warning mt-2">
                    {{ $totalProjects }}
                </h2>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted">
                    Total Invoices
                </small>
                <h2 class="font-weight-bold text-primary mt-2">
                    {{ $totalInvoices }}
                </h2>
            </div>
        </div>
    </div>

</div>

{{-- BARIS 3 --}}
<div class="row">

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted">Completed Projects</small>
                <h2 class="font-weight-bold text-success mt-2">
                    {{ $completedProjects }}
                </h2>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted">
                    In Progress Projects
                </small>
                <h2 class="font-weight-bold text-info mt-2">
                    {{ $progressProjects }}
                </h2>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted">
                    Pending Projects
                </small>

                <h2 class="font-weight-bold text-danger mt-2">
                    {{ $pendingProjects }}
                </h2>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted">Open Tickets</small>
                <h2 class="font-weight-bold text-warning mt-2">
                    {{ $openTickets }}
                </h2>
            </div>
        </div>
    </div>

</div>

{{-- BARIS 4 --}}
<div class="row">

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted">Paid Invoices</small>
                <h2 class="font-weight-bold text-primary mt-2">
                    {{ $paidInvoices }}
                </h2>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted">
                    Partial Invoices
                </small>
                <h2 class="font-weight-bold text-warning mt-2">
                    {{ $partialInvoices }}
                </h2>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted">Unpaid Invoices</small>
                <h2 class="font-weight-bold text-danger mt-2">
                    {{ $unpaidInvoices }}
                </h2>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted">
                    Overdue Invoices
                </small>
                <h2 class="font-weight-bold text-danger mt-2">
                    {{ $overdueInvoices }}
                </h2>
            </div>
        </div>
    </div>
</div>


{{-- BARIS 5 --}}
<div class="row">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted">
                    Reward Clients
                </small>
                <h2 class="font-weight-bold text-success mt-2">
                    {{ $rewardClients }}
                </h2>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted">
                    Closed Tickets
                </small>
                <h2 class="font-weight-bold text-success mt-2">
                    {{ $closedTickets }}
                </h2>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted">Total Payment Amount</small>
                <h2 class="font-weight-bold text-info mt-2">
                    Rp {{ number_format($totalPaymentAmount,0,',','.') }}
                </h2>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted">Projects Completion Rate</small>

                <h2 class="font-weight-bold text-primary">
                        {{ $projectCompletionRate }}%
                </h2>
                <div class="progress">
                    <div class="progress-bar bg-success"
                        style="width: {{ $projectCompletionRate }}%">
                    </div>
                </div>

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
