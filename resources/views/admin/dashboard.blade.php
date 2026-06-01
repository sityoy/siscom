@extends('layouts.admin')

@section('page-title', 'Dashboard')

@section('admin-content')

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
                <small class="text-muted">Total Users</small>
                <h2 class="font-weight-bold text-dark mt-2">
                    {{ $totalUsers }}
                </h2>
            </div>
        </div>
    </div>

</div>

<div class="row">

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <small class="text-muted">Total Porjects</small>
                <h2 class="font-weight-bold text-warning mt-2">
                    {{ $totalProjects }}
                </h2>
            </div>
        </div>
    </div>

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
                <small class="text-muted">Pending Projects</small>
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
                <small class="text-muted">Total Payments</small>
                <h2 class="font-weight-bold text-info mt-2">
                    {{ $totalPayments }}
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

</div>

    <div class="row">

        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <small class="text-muted">Clients Cashback Rewards</small>
                    <h2 class="font-weight-bold text-success mt-2">
                        Rp {{ number_format($totalCashback,0,',','.') }}
                    </h2>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
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
                        </tr>
                    </thead>

                    <tbody>

                    @forelse($latestInvoices as $invoice)

                        <tr>
                            <td>{{ $invoice->invoice_number }}</td>
                            <td>{{ $invoice->client->name ?? '-' }}</td>
                            <td>{{ ucfirst($invoice->status) }}</td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="3" class="text-center text-muted">
                                Not yet invoice
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
                            <th>Nominal</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>

                    <tbody>

                    @forelse($latestPayments as $payment)

                        <tr>
                            <td>{{ $payment->invoice->invoice_number ?? '-' }}</td>
                            <td>Rp {{ number_format($payment->amount,0,',','.') }}</td>
                            <td>{{ $payment->payment_date }}</td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="3" class="text-center text-muted">
                                Not yet payments
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

                            <th>Clients</th>
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

                            <th>Clients</th>
                            <th>Subjects</th>
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
