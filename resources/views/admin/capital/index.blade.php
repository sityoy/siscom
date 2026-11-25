@extends('layouts.admin')

@section('page-title', 'Modal Usaha')

@section('admin-content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title font-weight-bold">Ringkasan Modal dan Pendapatan</h2>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('capital.index') }}" class="form-inline mb-3">
            <label for="capital-month" class="mr-2 mb-2">Bulan</label>
            <input type="month" id="capital-month" name="month" value="{{ $month }}" class="form-control mr-2 mb-2">
            <label for="capital-filter-invoice" class="mr-2 mb-2">Invoice</label>
            <select id="capital-filter-invoice" name="invoice_id" class="custom-select mr-2 mb-2">
                <option value="">Semua invoice dan modal umum</option>
                @foreach($invoices as $invoice)
                    <option value="{{ $invoice->id }}" @selected((string) $invoiceId === (string) $invoice->id)>
                        {{ $invoice->invoice_number }} — {{ $invoice->client?->name ?? 'Client' }}
                    </option>
                @endforeach
            </select>
            <button class="btn btn-primary mr-2 mb-2" type="submit">Tampilkan</button>
            @if($month || $invoiceId)
                <a href="{{ route('capital.index') }}" class="btn btn-outline-secondary mb-2">Reset filter</a>
            @endif
        </form>
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted">Setoran Modal</div>
                    <strong class="h5">Rp {{ number_format((float) $capitalTotal, 0, ',', '.') }}</strong>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted"><a href="{{ route('payments.index') }}">Pembayaran Client Tercatat</a></div>
                    <strong class="h5">Rp {{ number_format((float) $paymentsTotal, 0, ',', '.') }}</strong>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="border border-primary rounded p-3 h-100">
                    <div class="text-muted">Total Dana Masuk</div>
                    <strong class="h5 text-primary">Rp {{ number_format((float) $cashInTotal, 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-3"><div class="border rounded p-3">Gaji dialokasikan<br><strong>{{ 'Rp '.number_format((float)$salaryTotal, 0, ',', '.') }}</strong></div></div>
            <div class="col-md-4 mb-3"><div class="border rounded p-3">Pendapatan bersih<br><strong>{{ 'Rp '.number_format((float)$netTotal, 0, ',', '.') }}</strong></div></div>
        </div>
        <p class="small text-muted mb-0">
            {{ $month ? 'Periode '.$month : 'Semua periode' }}. Total dana masuk menjumlahkan setoran modal aktif dan pembayaran client sampai hari ini.
        </p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title font-weight-bold">Pencocokan Modal per Invoice</h2>
    </div>
    <div class="card-body">
        <p class="text-muted small">Selisih bersih = pembayaran tercatat − modal aktif − gaji dialokasikan. Filter bulan mengikuti tanggal pembagian gaji. Alokasi lama tanpa tanggal tampil di Semua periode.</p>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th scope="col">Invoice dan Client</th>
                        <th scope="col" class="text-right">Nilai Invoice (Bruto)</th>
                        <th scope="col" class="text-right">Modal Teralokasi</th>
                        <th scope="col" class="text-right">Pembayaran Tercatat</th>
                        <th scope="col" class="text-right">Gaji</th>
                        <th scope="col" class="text-right">Selisih bersih</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoiceBalances as $balance)
                        <tr>
                            <td>
                                <a href="{{ route('capital.index', ['invoice_id' => $balance['invoice']->id, 'month' => $month]) }}">
                                    {{ $balance['invoice']->invoice_number }}
                                </a>
                                <small class="d-block text-muted">{{ $balance['invoice']->client?->name ?? 'Client' }}</small>
                                <a class="btn btn-sm btn-outline-primary mt-1" href="{{ route('salary.show', [$balance['invoice'], ...request()->only('month','invoice_id','invoice_page','page','per_page')]) }}">Pembagian gaji</a>
                            </td>
                            <td class="text-right text-nowrap">Rp {{ number_format((float) $balance['invoice']->grand_total, 0, ',', '.') }}</td>
                            <td class="text-right text-nowrap">Rp {{ number_format((float) $balance['capital'], 0, ',', '.') }}</td>
                            <td class="text-right text-nowrap">Rp {{ number_format((float) $balance['payments'], 0, ',', '.') }}</td>
                            <td class="text-right text-nowrap">Rp {{ number_format((float) $balance['salary'], 0, ',', '.') }}</td>
                            <td class="text-right text-nowrap font-weight-bold {{ $balance['difference'] < 0 ? 'text-danger' : '' }}">
                                {{ $balance['difference'] < 0 ? '−' : '' }}Rp {{ number_format(abs((float) $balance['difference']), 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada invoice.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $invoiceBalances->withQueryString()->onEachSide(1)->links() }}
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title font-weight-bold">Catat Setoran Modal</h2>
    </div>
    <div class="card-body">
        <p class="text-muted">Masukkan dana yang sudah disetor pemilik ke kas atau rekening usaha.</p>
        <form method="POST" action="{{ route('capital.store') }}">
            @csrf
            @include('admin.capital._fields', ['contribution' => null])
            <button type="submit" class="btn btn-primary">Simpan Setoran</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title font-weight-bold">Riwayat Setoran Modal</h2>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th scope="col">Tanggal</th>
                        <th scope="col">Sumber dan Catatan</th>
                        <th scope="col">Invoice</th>
                        <th scope="col" class="text-right">Nominal</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contributions as $contribution)
                        <tr>
                            <td class="text-nowrap">{{ $contribution->contribution_date->format('d/m/Y') }}</td>
                            <td class="text-break">
                                <strong>{{ $contribution->source }}</strong>
                                @if($contribution->notes)
                                    <small class="d-block text-muted">{{ $contribution->notes }}</small>
                                @endif
                            </td>
                            <td>
                                @if($contribution->invoice)
                                    <a href="{{ route('capital.index', ['invoice_id' => $contribution->invoice_id, 'month' => $month]) }}">
                                        {{ $contribution->invoice->invoice_number }}
                                    </a>
                                @elseif($contribution->invoice_reference)
                                    {{ $contribution->invoice_reference }} <small class="text-muted">(invoice dihapus)</small>
                                @else
                                    <span class="text-muted">Modal umum</span>
                                @endif
                            </td>
                            <td class="text-right text-nowrap">Rp {{ number_format((float) $contribution->amount, 0, ',', '.') }}</td>
                            <td>
                                @if($contribution->voided_at)
                                    <span class="badge badge-secondary">Dibatalkan</span>
                                    <small class="d-block text-muted">{{ $contribution->voided_at->format('d/m/Y H:i') }}</small>
                                @else
                                    <span class="badge badge-success">Tercatat</span>
                                @endif
                            </td>
                            <td class="text-right text-nowrap">
                                @unless($contribution->voided_at)
                                    <a href="{{ route('capital.edit', $contribution) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <form method="POST" action="{{ route('capital.destroy', $contribution) }}" class="d-inline"
                                          onsubmit="return confirm('Batalkan setoran dan keluarkan dari ringkasan dana masuk?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Batalkan</button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada setoran modal untuk filter ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('layouts.partials.pagination', ['paginator' => $contributions])
    </div>
</div>
@endsection
