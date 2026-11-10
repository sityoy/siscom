@php
    $isEdit = (bool) $invoice;
    $defaults = $invoiceDefaults ?? [];
    $selectedType = old('invoice_type', $invoice?->invoice_type ?? ($defaults['invoice_type'] ?? 'general'));
    $selectedClient = old('client_id', $invoice?->client_id ?? ($defaults['client_id'] ?? null));
    $selectedProject = old('project_id', $invoice?->project_id ?? ($defaults['project_id'] ?? null));
    $invoiceDate = old('invoice_date', $invoice?->invoice_date?->format('Y-m-d') ?? ($defaults['invoice_date'] ?? now()->format('Y-m-d')));
    $dueDate = old('due_date', $invoice?->due_date?->format('Y-m-d') ?? ($defaults['due_date'] ?? now()->addDays(7)->format('Y-m-d')));
    $items = [];

    if (old('description')) {
        foreach (old('description', []) as $index => $description) {
            $items[] = [
                'description' => $description,
                'item_type' => old('item_type.'.$index, 'goods'),
                'specification' => old('specification.'.$index),
                'qty' => old('qty.'.$index, 1),
                'unit' => old('unit.'.$index, 'Pcs'),
                'price' => old('price.'.$index, 0),
                'duration' => old('duration.'.$index, 1),
                'duration_type' => old('duration_type.'.$index, 'Hari'),
                'start_date' => old('start_date.'.$index),
                'end_date' => old('end_date.'.$index),
            ];
        }
    } elseif ($isEdit) {
        foreach ($invoice->items as $item) {
            $items[] = [
                'description' => $item->description,
                'item_type' => $item->item_type ?: 'service',
                'specification' => $item->specification,
                'qty' => $item->qty,
                'unit' => $item->unit ?: 'Paket',
                'price' => $item->price,
                'duration' => $item->duration ?: 1,
                'duration_type' => $item->duration_type ?: 'Hari',
                'start_date' => $item->start_date,
                'end_date' => $item->end_date,
            ];
        }
    } else {
        $items[] = [
            'description' => $defaults['description'] ?? '',
            'item_type' => $defaults['item_type'] ?? 'goods',
            'specification' => $defaults['specification'] ?? '',
            'qty' => 1,
            'unit' => $defaults['unit'] ?? 'Pcs',
            'price' => $defaults['price'] ?? 0,
            'duration' => $defaults['duration'] ?? 1,
            'duration_type' => $defaults['duration_type'] ?? 'Hari',
            'start_date' => $defaults['start_date'] ?? null,
            'end_date' => $defaults['end_date'] ?? null,
        ];
    }
@endphp

@if(session('warning'))
    <div class="alert alert-warning">{{ session('warning') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">
        <strong>Invoice belum dapat disimpan:</strong>
        <ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<form action="{{ $isEdit ? route('invoices.update', $invoice) : route('invoices.store') }}" method="POST" id="invoice-form">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="card border-primary">
        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <h4 class="font-weight-bold mb-1">{{ $isEdit ? 'Edit Invoice' : 'Buat Invoice Baru' }}</h4>
                <small class="text-muted">Pilih jenis invoice agar rincian barang, SPJ, atau periode jasa ditampilkan dengan benar.</small>
            </div>
            <span class="badge badge-primary p-2" id="invoice-type-badge"></span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Jenis Invoice <span class="text-danger">*</span></label>
                    <select name="invoice_type" id="invoice-type" class="form-control" required>
                        @foreach(\App\Models\Invoice::TYPES as $value => $label)
                            <option value="{{ $value }}" @selected($selectedType === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Client <span class="text-danger">*</span></label>
                    <select name="client_id" id="client-id" class="form-control" required>
                        <option value="">-- Pilih Client --</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" @selected((string) $selectedClient === (string) $client->id)>{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Project <span id="project-required" class="text-danger d-none">*</span></label>
                    <select name="project_id" id="project-id" class="form-control">
                        <option value="">-- Pilih Project --</option>
                        @foreach($projects as $project)
                            @php($usedByOtherInvoice = $project->invoices_count > 0 && (string) $selectedProject !== (string) $project->id)
                            <option value="{{ $project->id }}"
                                    data-client="{{ $project->client_id }}"
                                    data-title="{{ $project->title }}"
                                    data-budget="{{ $project->budget }}"
                                    data-late-active="{{ $project->late_fee_active ? 1 : 0 }}"
                                    data-late-fee="{{ $project->late_fee_per_month ?? 100000 }}"
                                    @disabled($usedByOtherInvoice)
                                    @selected((string) $selectedProject === (string) $project->id)>
                                {{ $project->title }}{{ $usedByOtherInvoice ? ' — Invoice sudah ada' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div id="spj-info" class="alert alert-warning d-none">
                <strong>Invoice SPJ/Rekanan:</strong> wajib memilih project. Item memakai rincian pengadaan—jenis barang/jasa, spesifikasi, volume, satuan, harga satuan, dan total—tanpa mengikuti durasi paket jasa/sewa.
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label>Nomor Invoice</label>
                    <input type="text" name="invoice_number" class="form-control"
                           value="{{ old('invoice_number', $invoice?->invoice_number ?? ('#'.date('dmY').'-'.rand(1000, 9999))) }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Tanggal Invoice</label>
                    <div class="input-group">
                        <input type="date" name="invoice_date" id="invoice-date" class="form-control" value="{{ $invoiceDate }}" required>
                        <div class="input-group-append"><button type="button" class="btn btn-outline-primary" id="invoice-date-today">Today</button></div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Jatuh Tempo</label>
                    <input type="date" name="due_date" id="due-date" class="form-control" value="{{ $dueDate }}" required>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div><h4 class="font-weight-bold mb-1">Rincian Item</h4><small class="text-muted">Isi setiap barang atau jasa sebagai baris terpisah.</small></div>
            <button type="button" class="btn btn-success btn-sm" id="add-item"><i class="fas fa-plus mr-1"></i> Tambah Item</button>
        </div>
        <div class="card-body" id="invoice-items">
            @foreach($items as $item)
                <div class="card border invoice-item {{ !$loop->last ? 'mb-3' : 'mb-0' }}">
                    <div class="card-header py-2 bg-light d-flex justify-content-between align-items-center">
                        <strong class="item-number">Item {{ $loop->iteration }}</strong>
                        <button type="button" class="btn btn-outline-danger btn-sm remove-item"><i class="fas fa-trash"></i></button>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label>Jenis Item</label>
                                <select name="item_type[]" class="form-control item-type" required>
                                    @foreach(\App\Models\InvoiceItem::TYPES as $value => $label)
                                        <option value="{{ $value }}" @selected($item['item_type'] === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5 mb-3">
                                <label>Nama/Deskripsi Item</label>
                                <input type="text" name="description[]" class="form-control description" value="{{ $item['description'] }}" placeholder="Contoh: Laptop untuk laboratorium" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Spesifikasi/Ruang Lingkup</label>
                                <textarea name="specification[]" class="form-control specification" rows="2" placeholder="Merek, tipe, ukuran, kapasitas, atau ruang lingkup pekerjaan">{{ $item['specification'] }}</textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 mb-3">
                                <label>Qty/Volume</label>
                                <input type="number" name="qty[]" class="form-control qty" min="1" step="1" value="{{ $item['qty'] }}" required>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label>Satuan</label>
                                <select name="unit[]" class="form-control unit" required>
                                    @foreach(\App\Models\InvoiceItem::UNITS as $unit)
                                        <option value="{{ $unit }}" @selected($item['unit'] === $unit)>{{ $unit }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Harga Satuan</label>
                                <input type="number" name="price[]" class="form-control price" min="0" step="1" value="{{ $item['price'] }}" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Total Item</label>
                                <input type="text" class="form-control total-display" readonly>
                            </div>
                        </div>

                        <div class="service-period border-top pt-3">
                            <small class="text-muted d-block mb-2">Periode layanan (khusus jasa/sewa, opsional untuk Invoice Umum)</small>
                            <div class="row">
                                <div class="col-md-2 mb-2">
                                    <label>Durasi</label>
                                    <input type="number" name="duration[]" class="form-control duration" min="1" value="{{ $item['duration'] }}">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label>Periode</label>
                                    <select name="duration_type[]" class="form-control duration-type">
                                        @foreach(['Hari', 'Bulan', 'Tahun'] as $durationType)
                                            <option value="{{ $durationType }}" @selected($item['duration_type'] === $durationType)>{{ $durationType }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label>Mulai Layanan</label>
                                    <input type="date" name="start_date[]" class="form-control start-date" value="{{ $item['start_date'] }}">
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label>Akhir Layanan</label>
                                    <input type="date" name="end_date[]" class="form-control end-date" value="{{ $item['end_date'] }}" readonly>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label>Ringkasan</label>
                                    <input type="text" class="form-control period-summary" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header bg-white"><h5 class="font-weight-bold mb-0">Pajak, Denda, dan Status</h5></div>
                <div class="card-body">
                    <div class="alert alert-info py-2">
                        <strong>{{ $company->tax_status_label }}</strong>
                        @if($company->usesUmkmFinal()) — PPh Final UMKM {{ number_format($company->income_tax_rate, 2, ',', '.') }}% @endif
                        @if(!$company->isPkp())<br>PPN otomatis 0 karena perusahaan berstatus Non-PKP.@endif
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3"><label>PPN (%)</label><input type="number" name="vat_percent" id="vat-percent" class="form-control" min="0" max="100" step="0.01" value="{{ old('vat_percent', $invoice?->vat_percent ?? ($defaults['vat_percent'] ?? 0)) }}" @readonly(!$company->isPkp())></div>
                        <div class="col-md-4 mb-3"><label>Biaya Layanan</label><input type="number" name="service_fee" id="service-fee" class="form-control" min="0" value="{{ old('service_fee', $invoice?->service_fee ?? ($defaults['service_fee'] ?? 0)) }}"></div>
                        <div class="col-md-4 mb-3"><label>Cashback (%)</label><input type="number" name="cashback" id="cashback" class="form-control" min="0" max="20" value="{{ old('cashback', $invoice?->cashback ?? 0) }}"></div>
                        <div class="col-md-4 mb-3">
                            <label>Denda Keterlambatan</label><input type="hidden" name="late_fee_active" value="0">
                            <div class="form-check mt-2"><input type="checkbox" name="late_fee_active" id="late-fee-active" class="form-check-input" value="1" @checked(old('late_fee_active', $invoice?->late_fee_active ?? ($defaults['late_fee_active'] ?? false)))><label class="form-check-label" for="late-fee-active">Aktifkan denda</label></div>
                        </div>
                        <div class="col-md-4 mb-3"><label>Denda per 30 Hari</label><input type="number" name="late_fee_per_month" id="late-fee-per-month" class="form-control" min="0" step="1000" value="{{ old('late_fee_per_month', $invoice?->late_fee_per_month ?? ($defaults['late_fee_per_month'] ?? 100000)) }}"></div>
                        <div class="col-md-4 mb-3">
                            <label>Status</label><select name="status" id="status" class="form-control" required>
                                @foreach(['unpaid' => 'Unpaid', 'partial' => 'Partial', 'paid' => 'Paid', 'cancelled' => 'Cancelled'] as $value => $label)<option value="{{ $value }}" @selected(old('status', $invoice?->status ?? 'unpaid') === $value)>{{ $label }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <input type="hidden" name="income_tax_withheld" value="0">
                            <div class="form-check"><input type="checkbox" name="income_tax_withheld" id="income-tax-withheld" class="form-check-input" value="1" @checked(old('income_tax_withheld', $invoice?->income_tax_withheld ?? ($defaults['income_tax_withheld'] ?? false))) @disabled(!$company->usesUmkmFinal())><label class="form-check-label" for="income-tax-withheld">PPh Final dipotong sekolah/client ({{ number_format($company->income_tax_rate, 2, ',', '.') }}%)</label></div>
                        </div>
                    </div>
                    <div class="form-group mt-3 mb-0"><label>Catatan Invoice</label><textarea name="notes" class="form-control" rows="4">{{ old('notes', $invoice?->notes ?? ($defaults['notes'] ?? '')) }}</textarea></div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border-primary sticky-top" style="top: 15px;">
                <div class="card-header bg-primary text-white"><h5 class="font-weight-bold mb-0">Ringkasan Invoice</h5></div>
                <div class="card-body">
                    <dl class="row mb-0"><dt class="col-7">Subtotal</dt><dd class="col-5 text-right" id="subtotal-view">Rp 0</dd><dt class="col-7">PPN</dt><dd class="col-5 text-right" id="vat-view">Rp 0</dd><dt class="col-7">Biaya Layanan</dt><dd class="col-5 text-right" id="service-view">Rp 0</dd><dt class="col-7 text-danger">Potongan PPh</dt><dd class="col-5 text-right text-danger" id="income-tax-view">Rp 0</dd></dl>
                    <hr><div class="d-flex justify-content-between"><strong>Total Bruto</strong><strong id="gross-view">Rp 0</strong></div><div class="d-flex justify-content-between mt-2"><strong class="text-primary">Pembayaran Bersih</strong><h5 class="text-primary font-weight-bold mb-0" id="net-view">Rp 0</h5></div>
                </div>
                <div class="card-footer d-flex justify-content-between"><a href="{{ route('invoices.index') }}" class="btn btn-secondary">Kembali</a><button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> {{ $isEdit ? 'Simpan Perubahan' : 'Buat Invoice' }}</button></div>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const invoiceType = document.getElementById('invoice-type');
    const projectSelect = document.getElementById('project-id');
    const clientSelect = document.getElementById('client-id');
    const taxRate = @json($company->usesUmkmFinal() ? (float) $company->income_tax_rate : 0);
    const today = @json(now()->format('Y-m-d'));
    const money = value => 'Rp ' + Math.round(value || 0).toLocaleString('id-ID');
    const typeLabels = @json(\App\Models\Invoice::TYPES);

    function updateEndDate(item) {
        const start = item.querySelector('.start-date').value;
        const duration = parseInt(item.querySelector('.duration').value, 10) || 0;
        const type = item.querySelector('.duration-type').value;
        item.querySelector('.period-summary').value = duration ? duration + ' ' + type : '-';
        if (!start || !duration) { item.querySelector('.end-date').value = ''; return; }
        const date = new Date(start + 'T00:00:00');
        if (type === 'Hari') date.setDate(date.getDate() + duration - 1);
        if (type === 'Bulan') { const day = date.getDate(); date.setDate(1); date.setMonth(date.getMonth() + duration); const last = new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate(); date.setDate(Math.min(day, last)); date.setDate(date.getDate() - 1); }
        if (type === 'Tahun') { date.setFullYear(date.getFullYear() + duration); date.setDate(date.getDate() - 1); }
        item.querySelector('.end-date').value = [date.getFullYear(), String(date.getMonth() + 1).padStart(2, '0'), String(date.getDate()).padStart(2, '0')].join('-');
    }

    function togglePeriod(item) {
        const itemType = item.querySelector('.item-type').value;
        const visible = invoiceType.value !== 'spj' && (invoiceType.value === 'project' || invoiceType.value === 'renewal' || itemType === 'service' || itemType === 'rental');
        item.querySelector('.service-period').style.display = visible ? '' : 'none';
    }

    function renumberItems() { document.querySelectorAll('.invoice-item').forEach((item, index) => { item.querySelector('.item-number').textContent = 'Item ' + (index + 1); togglePeriod(item); }); }
    function toggleInvoiceMode() {
        const isSpj = invoiceType.value === 'spj';
        document.getElementById('spj-info').classList.toggle('d-none', !isSpj);
        document.getElementById('project-required').classList.toggle('d-none', !isSpj);
        projectSelect.required = isSpj;
        document.getElementById('invoice-type-badge').textContent = typeLabels[invoiceType.value] || 'Invoice';
        renumberItems();
    }

    function calculateTotals() {
        let subtotal = 0;
        document.querySelectorAll('.invoice-item').forEach(item => { const qty = parseFloat(item.querySelector('.qty').value) || 0; const price = parseFloat(item.querySelector('.price').value) || 0; const total = qty * price; subtotal += total; item.querySelector('.total-display').value = money(total); updateEndDate(item); togglePeriod(item); });
        const vat = subtotal * (parseFloat(document.getElementById('vat-percent').value) || 0) / 100;
        const service = parseFloat(document.getElementById('service-fee').value) || 0;
        const gross = subtotal + vat + service;
        const withheld = document.getElementById('income-tax-withheld')?.checked || false;
        const incomeTax = withheld ? (subtotal + service) * taxRate / 100 : 0;
        document.getElementById('subtotal-view').textContent = money(subtotal); document.getElementById('vat-view').textContent = money(vat); document.getElementById('service-view').textContent = money(service); document.getElementById('income-tax-view').textContent = money(incomeTax); document.getElementById('gross-view').textContent = money(gross); document.getElementById('net-view').textContent = money(gross - incomeTax);
    }

    document.getElementById('invoice-date-today').addEventListener('click', () => document.getElementById('invoice-date').value = today);
    projectSelect.addEventListener('change', function () {
        const option = this.options[this.selectedIndex]; if (!option.value) return;
        clientSelect.value = option.dataset.client || '';
        if (!@json($isEdit) && document.querySelectorAll('.invoice-item').length === 1) { const item = document.querySelector('.invoice-item'); if (!item.querySelector('.description').value) item.querySelector('.description').value = option.dataset.title || ''; if (!(parseFloat(item.querySelector('.price').value) > 0)) item.querySelector('.price').value = option.dataset.budget || 0; }
        document.getElementById('late-fee-active').checked = option.dataset.lateActive === '1'; document.getElementById('late-fee-per-month').value = option.dataset.lateFee || 100000; calculateTotals();
    });
    document.getElementById('add-item').addEventListener('click', function () {
        const item = document.querySelector('.invoice-item').cloneNode(true); item.querySelectorAll('input, textarea').forEach(field => field.value = ''); item.querySelector('.qty').value = 1; item.querySelector('.unit').value = invoiceType.value === 'general' ? 'Pcs' : 'Paket'; item.querySelector('.item-type').value = invoiceType.value === 'general' ? 'goods' : 'service'; item.querySelector('.duration').value = 1; item.querySelector('.duration-type').value = 'Hari'; item.classList.remove('mb-0'); document.getElementById('invoice-items').appendChild(item); renumberItems(); calculateTotals();
    });
    document.addEventListener('click', function (event) { if (!event.target.closest('.remove-item')) return; if (document.querySelectorAll('.invoice-item').length === 1) return; event.target.closest('.invoice-item').remove(); renumberItems(); calculateTotals(); });
    document.addEventListener('input', calculateTotals);
    document.addEventListener('change', function (event) { if (event.target === invoiceType) toggleInvoiceMode(); calculateTotals(); });
    toggleInvoiceMode(); calculateTotals();
});
</script>
