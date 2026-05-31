@extends('layouts.client')

@section('page-title', 'Pembayaran Saya')

@section('client-content')

<div class="card border-0 shadow-sm">

    <div class="card-header bg-white border-0">

        <h4 class="mb-0 font-weight-bold">

            Histori Pembayaran

        </h4>

    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead>

                    <tr>

                        <th>#</th>
                        <th>Invoice</th>
                        <th>Metode</th>
                        <th>Tanggal</th>
                        <th>Nominal</th>
                        <th>Status</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($payments as $payment)

                        <tr>

                            <td>

                                {{ $loop->iteration }}

                            </td>

                            <td>

                                <strong>

                                    {{ $payment->invoice->invoice_number ?? '-' }}

                                </strong>

                            </td>

                            <td>

                                {{ $payment->payment_method ?? '-' }}

                            </td>

                            <td>

                                {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}

                            </td>

                            <td class="text-success font-weight-bold">

                                Rp {{ number_format($payment->amount,0,',','.') }}

                            </td>

                            <td>

                                <span class="badge bg-success">

                                    Paid

                                </span>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="6"
                                class="text-center text-muted py-5">

                                Belum ada pembayaran 😄🔥

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="mt-4">

            {{ $payments->links() }}

        </div>

    </div>

</div>

@endsection
