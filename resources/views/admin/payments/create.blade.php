@extends('layouts.admin')

@php
    $editing = isset($payment);
    $pageTitle = $editing ? 'Edit Pembayaran' : 'Tambah Pembayaran';
    $selected = old('invoice_id', $payment->invoice_id ?? $selectedInvoice ?? '');
    $amount = old('amount', $payment->amount ?? '');
@endphp

@section('page-title', $pageTitle)

@section('admin-content')
<div class="card">
    <div class="card-header">
        <h4 class="mb-0 font-weight-bold">{{ $pageTitle }}</h4>
    </div>

    <form action="{{ $editing ? route('payments.update', $payment) : route('payments.store') }}"
          method="POST" enctype="multipart/form-data">
        @csrf
        @if($editing)
            @method('PUT')
        @endif

        <div class="card-body">
            <div class="form-group">
                <label for="payment-invoice">Invoice</label>
                <select id="payment-invoice" name="invoice_id" class="form-control" required>
                    <option value="">{{ $invoices->isEmpty() ? 'Tidak ada invoice dengan sisa tagihan' : 'Pilih invoice' }}</option>
                    @foreach($invoices as $invoice)
                        <option value="{{ $invoice->id }}"
                                data-gross="{{ $invoice->grand_total }}"
                                data-tax="{{ $invoice->income_tax_withheld ? $invoice->income_tax_amount : 0 }}"
                                data-due-date="{{ $invoice->due_date?->toDateString() }}"
                                data-late-fee="{{ $invoice->late_fee_active ? $invoice->late_fee_per_month : 0 }}"
                                data-paid="{{ (float) $invoice->amount_paid - ($editing && $payment->invoice_id == $invoice->id ? $payment->amount : 0) }}"
                                @selected((string) $selected === (string) $invoice->id)>
                            {{ $invoice->invoice_number }} · {{ $invoice->client->signatureCompanyName() }} ·
                            {{ $invoice->project?->title ?? ($invoice->invoice_type === 'renewal' ? $invoice->type_label : 'Tanpa proyek') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="invoice-total">Tagihan pada Tanggal Bayar</label>
                    <input type="text" id="invoice-total" class="form-control" readonly>
                </div>
                <div class="form-group col-md-4">
                    <label for="paid-total">{{ $editing ? 'Pembayaran Lain' : 'Sudah Dibayar' }}</label>
                    <input type="text" id="paid-total" class="form-control" readonly>
                </div>
                <div class="form-group col-md-4">
                    <label for="remaining-total">Sisa Tagihan</label>
                    <input type="text" id="remaining-total" class="form-control" readonly>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="payment-date">Tanggal Pembayaran</label>
                    <input type="date" id="payment-date" name="payment_date" class="form-control" required
                           value="{{ old('payment_date', $editing ? \Carbon\Carbon::parse($payment->payment_date)->toDateString() : today()->toDateString()) }}">
                </div>
                <div class="form-group col-md-6">
                    <label for="payment-amount">Nominal Pembayaran</label>
                    <input type="text" id="payment-amount" name="amount" class="form-control" inputmode="decimal" required
                           value="{{ $amount }}" placeholder="Contoh: 896.500">
                </div>
                <div class="form-group col-md-6">
                    <label for="payment-method">Metode Pembayaran</label>
                    <select id="payment-method" name="payment_method" class="form-control" required>
                        @foreach(['Transfer Bank', 'Cash', 'E-Wallet', 'QRIS'] as $method)
                            <option value="{{ $method }}" @selected(old('payment_method', $payment->payment_method ?? 'Transfer Bank') === $method)>{{ $method }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="payment-proof">Bukti Pembayaran <small class="text-muted">(opsional)</small></label>
                    <input type="file" id="payment-proof" name="proof" class="form-control-file" accept="image/jpeg,image/png">
                    @if($editing && $payment->proof)
                        <small class="form-text text-muted">
                            <a href="{{ asset('storage/'.$payment->proof) }}" target="_blank" rel="noopener">Lihat bukti tersimpan</a>
                        </small>
                    @endif
                </div>
            </div>

            <div class="form-group mb-0">
                <label for="payment-notes">Catatan</label>
                <textarea id="payment-notes" name="notes" class="form-control" rows="3">{{ old('notes', $payment->notes ?? '') }}</textarea>
            </div>
        </div>

        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('payments.index') }}" class="btn btn-secondary">Kembali</a>
            <button type="submit" class="btn btn-primary" @disabled($invoices->isEmpty())>
                {{ $editing ? 'Simpan Perubahan' : 'Simpan Pembayaran' }}
            </button>
        </div>
    </form>
</div>

<script>
    const paymentInvoice = document.getElementById('payment-invoice');
    const paymentDate = document.getElementById('payment-date');
    const paymentMoney = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 });

    function showPaymentBalance() {
        const invoice = paymentInvoice.selectedOptions[0];
        const elapsedDays = (Date.parse(paymentDate.value) - Date.parse(invoice.dataset.dueDate)) / 86400000;
        const latePeriods = elapsedDays > 0 ? Math.ceil(elapsedDays / 30) : 0;
        const total = Math.max(0, Number(invoice.dataset.gross) + latePeriods * Number(invoice.dataset.lateFee || 0) - Number(invoice.dataset.tax));
        const paid = Number(invoice.dataset.paid);

        document.getElementById('invoice-total').value = invoice.value ? paymentMoney.format(total) : '';
        document.getElementById('paid-total').value = invoice.value ? paymentMoney.format(paid) : '';
        document.getElementById('remaining-total').value = invoice.value ? paymentMoney.format(Math.max(0, total - paid)) : '';
    }

    paymentInvoice.addEventListener('change', showPaymentBalance);
    paymentDate.addEventListener('input', showPaymentBalance);
    showPaymentBalance();
</script>
@endsection
