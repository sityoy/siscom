@extends('layouts.admin')

@section('page-title', 'Pengaturan Paket SPJ')

@section('admin-content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Data belum dapat disimpan:</strong>
        <ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h4 class="font-weight-bold mb-1">{{ $project->title }}</h4>
                <span class="text-muted">{{ $project->client->name }} · Invoice {{ $invoice->invoice_number }}</span>
            </div>

            <form method="POST" action="{{ route('procurement-documents.packages.update', $project) }}">
                @csrf
                @method('PUT')

                <div class="card-body">
                    <div class="alert alert-info">
                        Pilih <strong>SPJ Dikerjakan SIS.COM</strong> bila pekerjaan dilaksanakan langsung tanpa vendor utama.
                        Pilih <strong>SPJ Rekanan/Vendor</strong> bila ada pihak lain yang memasok barang atau melaksanakan bagian utama pekerjaan.
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Jenis Paket SPJ</label>
                            <select name="package_type" id="package-type" class="form-control" required>
                                @foreach(\App\Models\ProcurementPackage::TYPES as $value => $label)
                                    <option value="{{ $value }}" @selected(old('package_type', $package->package_type) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Metode Pengadaan</label>
                            <select name="procurement_method" class="form-control" required>
                                <option value="non_siplah" @selected(old('procurement_method', $package->procurement_method) === 'non_siplah')>Non-SIPLah/Langsung</option>
                                <option value="siplah" @selected(old('procurement_method', $package->procurement_method) === 'siplah')>SIPLah</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Status Paket</label>
                            <select name="status" class="form-control" required>
                                @foreach(\App\Models\ProcurementPackage::STATUSES as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', $package->status) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div id="vendor-section" class="border rounded p-3 mb-4">
                        <h6 class="font-weight-bold text-primary">Data Vendor Pihak Ketiga</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Nama Vendor</label>
                                <input type="text" name="vendor_name" id="vendor-name" class="form-control"
                                       value="{{ old('vendor_name', $package->vendor_name) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>NPWP/NIK Vendor</label>
                                <input type="text" name="vendor_tax_id" class="form-control"
                                       value="{{ old('vendor_tax_id', $package->vendor_tax_id) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Status Pajak Vendor</label>
                                <select name="vendor_tax_status" class="form-control">
                                    @foreach([
                                        'unknown' => 'Belum diverifikasi',
                                        'non_pkp' => 'Non-PKP',
                                        'pkp' => 'PKP',
                                        'pp55' => 'UMKM dengan Surat Keterangan PP 55',
                                        'individual' => 'Orang Pribadi',
                                    ] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('vendor_tax_status', $package->vendor_tax_status ?: 'unknown') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Menjadi dasar pemeriksaan pajak vendor; sistem tidak menebak tarif otomatis.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Kontak Vendor</label>
                                <input type="text" name="vendor_contact" class="form-control"
                                       value="{{ old('vendor_contact', $package->vendor_contact) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Alamat Vendor</label>
                                <textarea name="vendor_address" class="form-control" rows="2">{{ old('vendor_address', $package->vendor_address) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <h6 class="font-weight-bold">Pembagian Nilai dan Fee</h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Nilai Kontrak/Invoice</label>
                            <input type="number" name="contract_value" id="contract-value" class="form-control"
                                   value="{{ old('contract_value', $package->contract_value ?: $invoice->grand_total) }}" readonly required>
                            <small class="text-muted">Otomatis mengikuti invoice.</small>
                        </div>
                        <div class="col-md-4 mb-3 vendor-cost-field">
                            <label>Biaya Vendor (termasuk PPN bila ada)</label>
                            <input type="number" name="vendor_cost" id="vendor-cost" class="form-control" min="0" step="1"
                                   value="{{ old('vendor_cost', $package->vendor_cost ?? 0) }}">
                        </div>
                        <div class="col-md-4 mb-3 vendor-cost-field">
                            <label>Potongan Pajak Vendor</label>
                            <input type="number" name="vendor_tax_amount" id="vendor-tax" class="form-control" min="0" step="1"
                                   value="{{ old('vendor_tax_amount', $package->vendor_tax_amount ?? 0) }}">
                            <small class="text-muted">Isi sesuai bukti potong; bukan biaya tambahan.</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Biaya Operasional SIS.COM</label>
                            <input type="number" name="operational_cost" id="operational-cost" class="form-control" min="0" step="1"
                                   value="{{ old('operational_cost', $package->operational_cost ?? 0) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Partner Bisnis yang Sah</label>
                            <input type="text" name="partner_name" class="form-control"
                                   value="{{ old('partner_name', $package->partner_name) }}"
                                   placeholder="Kosongkan bila tidak ada">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Fee Partner</label>
                            <input type="number" name="partner_fee" id="partner-fee" class="form-control" min="0" step="1"
                                   value="{{ old('partner_fee', $package->partner_fee ?? 0) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Target Margin Bersih (%)</label>
                            <input type="number" name="target_fee_percent" id="target-fee" class="form-control" min="0" max="100" step="0.01"
                                   value="{{ old('target_fee_percent', $package->target_fee_percent ?? 10) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Pelunasan PPh Final</label>
                            <select name="tax_collection_method" class="form-control" required>
                                <option value="withheld" @selected(old('tax_collection_method', $package->tax_collection_method) === 'withheld')>Dipotong sekolah/bendahara</option>
                                <option value="self_paid" @selected(old('tax_collection_method', $package->tax_collection_method) === 'self_paid')>Disetor sendiri SIS.COM</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Estimasi PPh Final</label>
                            <input type="text" class="form-control" id="tax-preview" readonly>
                            <small class="text-muted">Tarif {{ number_format($company?->income_tax_rate ?? 0, 2, ',', '.') }}% mengikuti profil pajak perusahaan.</small>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label>Catatan Paket</label>
                        <textarea name="notes" class="form-control" rows="3"
                                  placeholder="Tuliskan ruang lingkup vendor, dasar fee, garansi, atau catatan pembayaran">{{ old('notes', $package->notes) }}</textarea>
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('procurement-documents.packages') }}" class="btn btn-secondary">Kembali</a>
                    <button class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan Pengaturan SPJ</button>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="font-weight-bold mb-0">Arsip Dokumen Vendor/Rekanan</h5>
            </div>
            <div class="card-body">
                @if($package->exists)
                    <form method="POST"
                          action="{{ route('procurement-documents.packages.files.store', $package) }}"
                          enctype="multipart/form-data"
                          class="border rounded p-3 mb-4">
                        @csrf
                        <div class="row align-items-end">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label>Kategori Dokumen</label>
                                <select name="category" class="form-control" required>
                                    @foreach(\App\Models\ProcurementPackageFile::CATEGORIES as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label>Judul Dokumen</label>
                                <input type="text" name="title" class="form-control" placeholder="Opsional">
                            </div>
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label>File</label>
                                <input type="file" name="file" class="form-control-file" required
                                       accept=".pdf,.jpg,.jpeg,.png,.xlsx,.xls,.doc,.docx">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-muted">PDF, gambar, Excel, atau Word; maksimal 10 MB. File disimpan privat.</small>
                            <button class="btn btn-primary"><i class="fas fa-upload mr-1"></i> Unggah Dokumen</button>
                        </div>
                    </form>

                    @if($package->files->isEmpty())
                        <div class="text-center text-muted py-3">Belum ada dokumen vendor yang diarsipkan.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Kategori</th>
                                        <th>Judul/File</th>
                                        <th>Pengunggah</th>
                                        <th>Tanggal</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($package->files as $file)
                                        <tr>
                                            <td>{{ $file->category_label }}</td>
                                            <td>
                                                <strong>{{ $file->title }}</strong><br>
                                                <small class="text-muted">{{ $file->original_name }} · {{ number_format($file->file_size / 1024, 0, ',', '.') }} KB</small>
                                            </td>
                                            <td>{{ $file->uploader?->name ?: '-' }}</td>
                                            <td>{{ $file->created_at?->format('d/m/Y H:i') }}</td>
                                            <td class="text-center text-nowrap">
                                                <a href="{{ route('procurement-documents.packages.files.download', $file) }}"
                                                   class="btn btn-sm btn-outline-primary" title="Unduh">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <form method="POST"
                                                      action="{{ route('procurement-documents.packages.files.destroy', $file) }}"
                                                      class="d-inline"
                                                      onsubmit="return confirm('Hapus dokumen pendukung ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                @else
                    <div class="alert alert-info mb-0">
                        Simpan pengaturan paket terlebih dahulu, lalu bagian unggah dokumen vendor akan tersedia.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card sticky-top" style="top: 20px;">
            <div class="card-header"><h5 class="font-weight-bold mb-0">Simulasi Pembagian</h5></div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-7">Nilai Kontrak</dt><dd class="col-5 text-right" id="summary-contract">Rp 0</dd>
                    <dt class="col-7">Biaya Vendor</dt><dd class="col-5 text-right text-danger" id="summary-vendor">Rp 0</dd>
                    <dt class="col-7">Bayar Bersih Vendor</dt><dd class="col-5 text-right" id="summary-vendor-net">Rp 0</dd>
                    <dt class="col-7">Margin Kotor SIS</dt><dd class="col-5 text-right font-weight-bold" id="summary-gross">Rp 0</dd>
                    <dt class="col-7">Operasional</dt><dd class="col-5 text-right text-danger" id="summary-operational">Rp 0</dd>
                    <dt class="col-7">Fee Partner</dt><dd class="col-5 text-right text-danger" id="summary-partner">Rp 0</dd>
                    <dt class="col-7">PPh Final</dt><dd class="col-5 text-right text-danger" id="summary-tax">Rp 0</dd>
                </dl>
                <hr>
                <div class="d-flex justify-content-between align-items-end">
                    <div><small class="text-muted">Margin Bersih SIS.COM</small><h4 class="font-weight-bold text-success mb-0" id="summary-net">Rp 0</h4></div>
                    <span class="badge badge-primary p-2" id="summary-percent">0%</span>
                </div>
                <div class="alert alert-warning mt-3 mb-0" id="target-warning">
                    Margin bersih masih di bawah target.
                </div>
            </div>
        </div>

        <div class="card border-warning">
            <div class="card-body">
                <strong>Pedoman internal yang disarankan</strong>
                <ul class="pl-3 mb-0 mt-2 small">
                    <li>5-7,5%: administrasi/referral ringan.</li>
                    <li>10-15%: koordinasi, dukungan, dan garansi.</li>
                    <li>15-20%: implementasi penuh atau risiko pembiayaan.</li>
                </ul>
                <small class="d-block text-muted mt-2">Fee harus tercantum dalam harga/kontrak dan didukung pekerjaan nyata.</small>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const packageType = document.getElementById('package-type');
    const vendorSection = document.getElementById('vendor-section');
    const vendorName = document.getElementById('vendor-name');
    const vendorCostField = document.querySelector('.vendor-cost-field');
    const vendorCostInput = document.getElementById('vendor-cost');
    const taxRate = @json((float) ($company?->income_tax_rate ?? 0));
    const taxBase = @json((float) ($invoice->income_tax_base ?: $invoice->grand_total));

    const money = value => 'Rp ' + Math.round(value).toLocaleString('id-ID');

    function toggleVendor() {
        const thirdParty = packageType.value === 'third_party';
        vendorSection.style.display = thirdParty ? '' : 'none';
        vendorCostField.style.display = thirdParty ? '' : 'none';
        vendorName.required = thirdParty;
        if (!thirdParty) vendorCostInput.value = 0;
        calculate();
    }

    function calculate() {
        const contract = Number(document.getElementById('contract-value').value) || 0;
        const vendor = packageType.value === 'third_party' ? (Number(vendorCostInput.value) || 0) : 0;
        const vendorTax = packageType.value === 'third_party' ? (Number(document.getElementById('vendor-tax').value) || 0) : 0;
        const operational = Number(document.getElementById('operational-cost').value) || 0;
        const partner = Number(document.getElementById('partner-fee').value) || 0;
        const target = Number(document.getElementById('target-fee').value) || 0;
        const tax = taxBase * taxRate / 100;
        const gross = contract - vendor;
        const net = gross - operational - partner - tax;
        const percent = contract > 0 ? (net / contract) * 100 : 0;

        document.getElementById('tax-preview').value = money(tax);
        document.getElementById('summary-contract').textContent = money(contract);
        document.getElementById('summary-vendor').textContent = money(vendor);
        document.getElementById('summary-vendor-net').textContent = money(Math.max(0, vendor - vendorTax));
        document.getElementById('summary-gross').textContent = money(gross);
        document.getElementById('summary-operational').textContent = money(operational);
        document.getElementById('summary-partner').textContent = money(partner);
        document.getElementById('summary-tax').textContent = money(tax);
        document.getElementById('summary-net').textContent = money(net);
        document.getElementById('summary-net').className = 'font-weight-bold mb-0 ' + (net >= 0 ? 'text-success' : 'text-danger');
        document.getElementById('summary-percent').textContent = percent.toLocaleString('id-ID', { maximumFractionDigits: 2 }) + '%';
        document.getElementById('target-warning').style.display = percent < target ? '' : 'none';
    }

    packageType.addEventListener('change', toggleVendor);
    document.querySelectorAll('input, select').forEach(element => {
        element.addEventListener('input', calculate);
        element.addEventListener('change', calculate);
    });

    toggleVendor();
});
</script>

@endsection
