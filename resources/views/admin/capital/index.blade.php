@extends('layouts.admin')

@section('page-title', 'Modal Usaha')

@section('admin-content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title font-weight-bold">Ringkasan Dana Masuk</h2>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('capital.index') }}" class="form-inline mb-3">
            <label for="capital-month" class="mr-2">Bulan</label>
            <input type="month" id="capital-month" name="month" value="{{ $month }}" class="form-control mr-2">
            <button class="btn btn-primary mr-2" type="submit">Tampilkan</button>
            @if($month)
                <a href="{{ route('capital.index') }}" class="btn btn-outline-secondary">Semua periode</a>
            @endif
        </form>
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted">Setoran Modal</div>
                    <strong class="h5">Rp {{ number_format((float) $capitalTotal, 2, ',', '.') }}</strong>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="border rounded p-3 h-100">
                    <div class="text-muted"><a href="{{ route('payments.index') }}">Pembayaran Client Tercatat</a></div>
                    <strong class="h5">Rp {{ number_format((float) $paymentsTotal, 2, ',', '.') }}</strong>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="border border-primary rounded p-3 h-100">
                    <div class="text-muted">Total Dana Masuk</div>
                    <strong class="h5 text-primary">Rp {{ number_format((float) $cashInTotal, 2, ',', '.') }}</strong>
                </div>
            </div>
        </div>
        <p class="small text-muted mb-0">
            {{ $month ? 'Periode '.$month : 'Semua periode' }}. Ringkasan menghitung setoran modal aktif dan pembayaran client yang tercatat sampai hari ini, sebelum dikurangi pengeluaran.
        </p>
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
                            <td class="text-right text-nowrap">Rp {{ number_format((float) $contribution->amount, 2, ',', '.') }}</td>
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
                        <tr><td colspan="5" class="text-center text-muted py-4">Belum ada setoran modal dalam periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('layouts.partials.pagination', ['paginator' => $contributions])
    </div>
</div>
@endsection
