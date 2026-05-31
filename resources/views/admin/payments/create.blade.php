@extends('layouts.admin')

@section('page-title', 'Tambah Pembayaran')

@section('admin-content')

<div class="card">

    <div class="card-header">

        <h4 class="mb-0 font-weight-bold">

            Tambah Pembayaran

        </h4>

        <small class="text-muted">

            Tambahkan pembayaran invoice client

        </small>

    </div>

    <form action="{{ route('payments.store') }}"
          method="POST">

        @csrf

        <div class="card-body">

            <div class="row">

                {{-- INVOICE --}}
                <div class="col-md-6 mb-3">

                    <label>Invoice</label>

                    <select name="invoice_id"
                            class="form-control"
                            required>

                        <option value="">
                            -- Pilih Invoice --
                        </option>

                        @foreach($invoices as $invoice)

                            <option value="{{ $invoice->id }}">

                                {{ $invoice->invoice_number }}
                                -
                                {{ $invoice->client->name }}

                            </option>

                        @endforeach

                    </select>

                </div>

                {{-- PAYMENT DATE --}}
                <div class="col-md-6 mb-3">

                    <label>Tanggal Pembayaran</label>

                    <input type="date"
                           name="payment_date"
                           class="form-control"
                           value="{{ date('Y-m-d') }}"
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
                           placeholder="0"
                           required>

                </div>

                {{-- METHOD --}}
                <div class="col-md-6 mb-3">

                    <label>Metode Pembayaran</label>

                    <select name="payment_method"
                            class="form-control">

                        <option value="Transfer Bank">
                            Transfer Bank
                        </option>

                        <option value="Cash">
                            Cash
                        </option>

                        <option value="E-Wallet">
                            E-Wallet
                        </option>

                        <option value="QRIS">
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
                          rows="4"
                          placeholder="Catatan pembayaran"></textarea>

            </div>

        </div>

        <div class="card-footer d-flex justify-content-between">

            <a href="{{ route('payments.index') }}"
               class="btn btn-secondary">

                Kembali

            </a>

            <button class="btn btn-primary">

                Simpan Pembayaran

            </button>

        </div>

    </form>

</div>

@endsection
