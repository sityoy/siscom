@extends('layouts.admin')

@section('page-title', 'Add Payment')

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
      method="POST"
      enctype="multipart/form-data">

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
                            -- Choose Invoice --
                        </option>

                        @foreach($invoices as $invoice)

                            <option value="{{ $invoice->id }}"
                                    data-total="{{ $invoice->grand_total }}"
                                    data-paid="{{ $invoice->payments->sum('amount') }}">

                                {{ $invoice->invoice_number }}
-
                                {{ $invoice->client->name }}
                                -
                                Rp {{ number_format($invoice->grand_total,0,',','.') }}
                                -
                                {{ $invoice->client->name }}

                            </option>

                        @endforeach

                    </select>

                    <div class="row">

                        <div class="col-md-4">
                            <br>
                            <label>Total Invoice</label>

                            <input type="text"
                                id="invoice-total"
                                class="form-control"
                                readonly >

                        </div>

                        <div class="col-md-4">

                            <br>
                            <label>Sudah Dibayar</label>

                            <input type="text"
                                id="paid-total"
                                class="form-control"
                                readonly >

                        </div>

                        <div class="col-md-4">

                            <br>
                            <label>Sisa Tagihan</label>

                            <input type="text"
                                id="remaining-total"
                                class="form-control"
                                readonly>

                        </div>

                    </div>

                </div>

                {{-- PAYMENT DATE --}}
                <div class="col-md-6 mb-3">

                    <label>Tanggal Pembayaran</label>

                    <input type="date"
                           name="payment_date"
                           class="form-control"
                           value="{{ date('Y-m-d') }}"
                           required required>

                    <div class="col-md-12 mb-3">
                        <br>
                        <label>Bukti Pembayaran</label>

                        <input type="file"
                            name="proof"
                            class="form-control" required>

                    </div>

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
                            class="form-control" required>

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

<script>

document
.querySelector('[name="invoice_id"]')
.addEventListener('change', function(){

    let option =
        this.options[this.selectedIndex];

    let total =
        parseFloat(
            option.dataset.total || 0
        );

    let paid =
        parseFloat(
            option.dataset.paid || 0
        );

    let remaining =
        total - paid;

    document
        .getElementById('invoice-total')
        .value =
        total.toLocaleString('id-ID');

    document
        .getElementById('paid-total')
        .value =
        paid.toLocaleString('id-ID');

    document
        .getElementById('remaining-total')
        .value =
        remaining.toLocaleString('id-ID');

});

</script>

@endsection
