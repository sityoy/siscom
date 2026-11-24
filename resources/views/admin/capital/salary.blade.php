@extends('layouts.admin')
@section('page-title', 'Pembagian Gaji')
@section('admin-content')
@php($rp = fn ($n) => 'Rp '.number_format((float)(string)$n, 2, ',', '.'))
<div class="card"><div class="card-body">
<a href="{{ route('capital.index', ['invoice_id'=>$invoice->id]) }}">Kembali ke Modal Usaha</a>
<h2 class="h4 mt-3">Pembagian gaji · {{ $invoice->invoice_number }}</h2>
<p>Alokasi dihitung dari seluruh pembayaran yang diterima sampai hari ini dikurangi modal aktif invoice. Persentase mengikuti perubahan laba. Nominal tetap mengikuti nilai yang Anda masukkan.</p>
<dl><dt>Pembayaran diterima</dt><dd>{{ $rp($payments) }}</dd><dt>Modal dialokasikan</dt><dd>{{ $rp($capital) }}</dd><dt>Laba sebelum pembagian</dt><dd>{{ $rp($profit) }}</dd><dt>Sisa untuk dibagikan</dt><dd>{{ $rp($remaining) }}</dd></dl>
@if($remaining->isNegative())<p class="alert alert-warning">Pembayaran atau modal telah berubah. Sesuaikan alokasi untuk menutup kekurangan.</p>@endif
<div class="table-responsive"><table class="table"><thead><tr><th>Penerima</th><th>Dasar pembagian</th><th>Alokasi gaji</th><th></th></tr></thead><tbody>
@forelse($rows as $row)<tr><td>{{ $row->recipient }}</td><td>{{ $row->mode === 'percent' ? $row->value.'%' : 'Nominal tetap' }}</td><td>{{ $rp($row->amount) }}</td><td><form action="{{ route('salary.destroy', [$invoice,$row->id]) }}" method="post">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Hapus alokasi</button></form></td></tr>
@empty<tr><td colspan="4">Belum ada alokasi gaji.</td></tr>@endforelse
</tbody></table></div>
<form method="post" action="{{ route('salary.store',$invoice) }}">@csrf
<label>Penerima <input class="form-control" name="recipient" maxlength="100" required value="{{ old('recipient') }}"></label>
<label>Metode <select name="mode" class="form-control"><option value="percent">Persen laba (%)</option><option value="fixed" @selected(old('mode')==='fixed')>Nominal (Rp)</option></select></label>
<label>Nilai <input type="number" name="value" class="form-control" min="0.01" step="0.01" value="{{ old('value') }}" required></label>
<button class="btn btn-primary">Simpan alokasi</button>
<p class="small text-muted">Untuk mengubah alokasi, masukkan nama penerima yang sama. Pencatatan ini merupakan rencana pembagian gaji.</p></form></div></div>
@endsection
