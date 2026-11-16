@extends('layouts.admin')

@section('page-title', 'Add Payment')

@section('admin-content')

<div class="card">

    <div class="card-header">

        <h4 class="mb-0 font-weight-bold">

            {{ isset($payment) ? 'Edit Pembayaran' : 'Tambah Pembayaran' }}

        </h4>

        <small class="text-muted">

            {{ isset($payment) ? 'Perbarui data pembayaran invoice client' : 'Tambahkan pembayaran invoice client' }}

        </small>

    </div>

    <form action="{{ isset($payment) ? route('payments.update', $payment) : route('payments.store') }}"
      method="POST"
      enctype="multipart/form-data">

        @csrf
        @isset($payment)
            @method('PUT')
        @endisset

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
                                    data-total="{{ $invoice->net_receivable }}"
                                    data-paid="{{ $invoice->payments->where('id', '!=', $payment->id ?? null)->sum('amount') }}"
                                    @selected((string) old('invoice_id', $payment->invoice_id ?? '') === (string) $invoice->id)>

                                {{ $invoice->invoice_number }}
-
                                {{ $invoice->client->name }}
                                -
                                Rp {{ number_format($invoice->net_receivable,0,',','.') }}

                                @if($invoice->late_fee_amount > 0)
                                    (Denda Rp {{ number_format($invoice->late_fee_amount,0,',','.') }})
                                @endif
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
                           value="{{ old('payment_date', isset($payment) ? \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') : date('Y-m-d')) }}"
                           required>

                    <div class="col-md-12 mb-3">
                        <br>
                        <label>Bukti Pembayaran</label>

                        <input type="file"
                            name="proof"
                            class="form-control"
                            @required(!isset($payment))>

                        @if(isset($payment) && $payment->proof)
                            <small class="form-text text-muted">
                                Bukti lama tetap digunakan jika tidak memilih file baru.
                                <a href="{{ asset('storage/'.$payment->proof) }}" target="_blank">Lihat bukti</a>
                            </small>
                        @endif

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
                           value="{{ old('amount', $payment->amount ?? '') }}"
                           placeholder="0"
                           required>

                </div>

                {{-- METHOD --}}
                <div class="col-md-6 mb-3">

                    <label>Metode Pembayaran</label>

                    <select name="payment_method"
                            class="form-control" required>

                        <option value="Transfer Bank" @selected(old('payment_method', $payment->payment_method ?? 'Transfer Bank') === 'Transfer Bank')>
                            Transfer Bank
                        </option>

                        <option value="Cash" @selected(old('payment_method', $payment->payment_method ?? '') === 'Cash')>
                            Cash
                        </option>

                        <option value="E-Wallet" @selected(old('payment_method', $payment->payment_method ?? '') === 'E-Wallet')>
                            E-Wallet
                        </option>

                        <option value="QRIS" @selected(old('payment_method', $payment->payment_method ?? '') === 'QRIS')>
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
                          placeholder="Catatan pembayaran">{{ old('notes', $payment->notes ?? '') }}</textarea>

            </div>

        </div>

        <div class="card-footer d-flex justify-content-between">

            <a href="{{ route('payments.index') }}"
               class="btn btn-secondary">

                Kembali

            </a>

            <button class="btn btn-primary">

                {{ isset($payment) ? 'Simpan Perubahan' : 'Simpan Pembayaran' }}

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

document
    .querySelector('[name="invoice_id"]')
    .dispatchEvent(new Event('change'));

</script>

@endsection
