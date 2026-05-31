@extends('layouts.admin')

@section('page-title', 'Edit Pembayaran')

@section('admin-content')

<div class="card">

    <div class="card-header">

        <h4 class="mb-0 font-weight-bold">

            Edit Pembayaran

        </h4>

        <small class="text-muted">

            Perbarui data pembayaran invoice

        </small>

    </div>

    <form action="{{ route('payments.update', $payment->id) }}"
          method="POST">

        @csrf
        @method('PUT')

        <div class="card-body">

            <div class="row">

                {{-- INVOICE --}}
                <div class="col-md-6 mb-3">

                    <label>Invoice</label>

                    <select name="invoice_id"
                            class="form-control"
                            required>

                        @foreach($invoices as $invoice)

                            <option value="{{ $invoice->id }}"
                                @selected($payment->invoice_id == $invoice->id)>

                                {{ $invoice->invoice_number }}
                                -
                                {{ $invoice->client->name }}

                            </option>

                        @endforeach

                    </select>

                </div>

                {{-- DATE --}}
                <div class="col-md-6 mb-3">

                    <label>Tanggal Pembayaran</label>

                    <input type="date"
                           name="payment_date"
                           class="form-control"
                           value="{{ old('payment_date', $payment->payment_date) }}"
                           required>

                </div>

            </div>

            <div class="row">

                {{-- AMOUNT --}}
                <div class="col-md-6 mb-3">

                    <label>Nominal Pembayaran</label>

                    <input type="number"
                           name="amount"
                           class="form-control"
                           value="{{ old('amount', $payment->amount) }}"
                           required>

                </div>

                {{-- METHOD --}}
                <div class="col-md-6 mb-3">

                    <label>Metode Pembayaran</label>

                    <select name="payment_method"
                            class="form-control">

                        <option value="Transfer Bank"
                            @selected($payment->payment_method == 'Transfer Bank')>

                            Transfer Bank

                        </option>

                        <option value="Cash"
                            @selected($payment->payment_method == 'Cash')>

                            Cash

                        </option>

                        <option value="E-Wallet"
                            @selected($payment->payment_method == 'E-Wallet')>

                            E-Wallet

                        </option>

                        <option value="QRIS"
                            @selected($payment->payment_method == 'QRIS')>

                            QRIS

                        </option>

                    </select>

                </div>

            </div>

            {{-- NOTES --}}
            <div class="mb-3">

                <label>Catatan</label>

                <textarea name="notes"
                          class="form-control"
                          rows="4">{{ old('notes', $payment->notes) }}</textarea>

            </div>

        </div>

        <div class="card-footer d-flex justify-content-between">

            <a href="{{ route('payments.index') }}"
               class="btn btn-secondary">

                Kembali

            </a>

            <button class="btn btn-primary">

                Update Pembayaran

            </button>

        </div>

    </form>

</div>

@endsection
