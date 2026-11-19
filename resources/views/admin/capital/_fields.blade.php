<div class="form-row">
    <div class="form-group col-md-3">
        <label for="capital-date">Tanggal Setoran</label>
        <input type="date" id="capital-date" name="contribution_date" class="form-control"
               max="{{ today()->toDateString() }}"
               value="{{ old('contribution_date', $contribution?->contribution_date->toDateString() ?? today()->toDateString()) }}" required>
    </div>
    <div class="form-group col-md-5">
        <label for="capital-source">Sumber Setoran</label>
        <input type="text" id="capital-source" name="source" class="form-control" maxlength="120"
               value="{{ old('source', $contribution?->source) }}" placeholder="Nama pemilik atau sumber setoran" required>
    </div>
    <div class="form-group col-md-4">
        <label for="capital-amount">Nominal Setoran (Rp)</label>
        <input type="text" id="capital-amount" name="amount" inputmode="decimal" class="form-control" maxlength="24"
               value="{{ old('amount', $contribution ? number_format((float) $contribution->amount, 2, ',', '.') : '') }}"
               placeholder="1.500.000,50" required>
    </div>
</div>
<div class="form-group">
    <label for="capital-invoice">Invoice Terkait <small class="text-muted">(opsional)</small></label>
    @if($contribution?->invoice_id || $contribution?->invoice_reference)
        <input id="capital-invoice" type="text" class="form-control" readonly
               value="{{ $contribution->invoice?->invoice_number ?? $contribution->invoice_reference }}{{ $contribution->invoice_id ? '' : ' (invoice dihapus)' }}"
               aria-describedby="capital-invoice-help">
        <small id="capital-invoice-help" class="form-text text-muted">Untuk mengganti invoice, batalkan setoran ini lalu catat setoran baru.</small>
    @else
        <select id="capital-invoice" name="invoice_id" class="custom-select" aria-describedby="capital-invoice-help">
            <option value="">Modal umum — tanpa invoice</option>
            @foreach($invoices as $invoice)
                <option value="{{ $invoice->id }}"
                        @disabled($invoice->has_active_capital)
                        @selected(!$invoice->has_active_capital && (string) old('invoice_id', $invoiceId ?? null) === (string) $invoice->id)>
                    {{ $invoice->invoice_number }} — {{ $invoice->client?->name ?? 'Client' }}{{ $invoice->has_active_capital ? ' (sudah tercatat)' : '' }}
                </option>
            @endforeach
        </select>
        <small id="capital-invoice-help" class="form-text text-muted">Satu setoran aktif per invoice. Perbarui nominal melalui Edit, atau batalkan setoran untuk mencatatnya kembali.</small>
    @endif
</div>
<div class="form-group">
    <label for="capital-notes">Catatan atau Referensi Transfer <small class="text-muted">(opsional)</small></label>
    <textarea id="capital-notes" name="notes" class="form-control" maxlength="1000" rows="2">{{ old('notes', $contribution?->notes) }}</textarea>
</div>
