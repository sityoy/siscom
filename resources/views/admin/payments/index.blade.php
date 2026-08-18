@extends('layouts.admin')

@section('page-title', 'Payments')

@section('admin-content')

<div class="card">

    <div class="card-header d-flex justify-content-between align-items-center">

        <div>

            <h4 class="mb-0 font-weight-bold">

                Payment Management

            </h4>

            <small class="text-muted">

                Kelola seluruh pembayaran invoice SIS.COM

            </small>

        </div>

        <div>

            <a href="{{ route('payments.export') }}"
               class="btn btn-success">

                Export Excel

            </a>

            <a href="{{ route('payments.create') }}"
               class="btn btn-primary">

                + Tambah Pembayaran

            </a>

        </div>

    </div>

    <div class="card-body">

        @if(session('success'))

            <div class="alert alert-success">

                {{ session('success') }}

            </div>

        @endif

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead>

                    <tr>

                        <th>#</th>
                        <th>Invoice</th>
                        <th>Client</th>
                        <th>Nominal</th>
                        <th>Tanggal</th>
                        <th>Metode</th>
                        <th>Proof</th>
                        <th>Status</th>
                        <th class="text-center">

                            Aksi

                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($payments as $payment)

                        @php

                            $invoice = $payment->invoice;

                            $totalPaid =
                                $invoice->payments->sum('amount');

                            $remaining =
                                $invoice->grand_total - $totalPaid;

                        @endphp

                        <tr>

                            <td>

                                {{ $loop->iteration }}

                            </td>

                            <td>

                                <div class="font-weight-bold">

                                    {{ $invoice->invoice_number }}

                                </div>

                                <small class="text-muted">

                                    Invoice SIS.COM

                                </small>

                            </td>

                            <td>

                                {{ $invoice->client->name ?? '-' }}

                            </td>

                            <td>

                                <span class="font-weight-bold text-success">

                                    Rp {{ number_format($payment->amount,0,',','.') }}

                                </span>

                            </td>

                            <td>

                                {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}

                            </td>

                            <td>

                                {{ $payment->payment_method ?? '-' }}

                            </td>

                            <td>

                            @if($payment->proof)

                                <a href="{{ asset('storage/'.$payment->proof) }}"
                                target="_blank">

                                    View

                                </a>

                            @else

                                -

                            @endif

                            </td>

                            <td>

                                @if($remaining <= 0)

                                    <span class="badge bg-success">

                                        Lunas

                                    </span>

                                @else

                                    <span class="badge bg-warning">

                                        Partial

                                    </span>

                                @endif

                            </td>

                            <td class="text-center">

                                <a href="{{ route('payments.edit', $payment->id) }}"
                                   class="btn btn-warning btn-sm">

                                    Edit

                                </a>

                                <form action="{{ route('payments.destroy', $payment->id) }}"
                                      method="POST"
                                      class="d-inline">

                                    @csrf
                                    @method('DELETE')

                                    <button
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('Yakin hapus pembayaran?')">

                                        Hapus

                                    </button>

                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="9"
                                class="text-center text-muted py-4">

                                Data pembayaran kosong

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
