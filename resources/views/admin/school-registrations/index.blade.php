@extends('layouts.admin')
@section('page-title', 'Pendaftaran Sekolah')
@section('admin-content')
<h1 class="h3">Pendaftaran Sekolah</h1>
@if($unavailable ?? false)
    <div class="alert alert-warning">Daftar belum dapat dibuka. Periksa migrasi SISCOM dan akses baca ke db_portalsiscom.schools.</div>
@else
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif
    <div class="card"><div class="card-body">
        <h2 class="h5">Menunggu verifikasi ({{ $pending->total() }})</h2>
        <p>Cocokkan sekolah dan kontak pendaftar dengan pesanan klien sebelum mengaktifkan layanan. Untuk sekolah baru, <a href="{{ route('clients.create') }}">tambahkan klien</a> terlebih dahulu.</p>
        @forelse($pending as $school)
            <details class="border rounded p-3 mb-2">
                <summary><strong>{{ $school->name }}</strong> · {{ strtoupper($school->code) }}</summary>
                <p class="mt-3">Kontak pendaftar: {{ $school->contact }}</p>
                <form method="POST" action="{{ route('school-registrations.approve', $school->id) }}">
                    @csrf
                    <label for="c-{{ $school->id }}">Klien SISCOM sesuai kontrak</label>
                    <select class="form-control mb-3" name="client_id" id="c-{{ $school->id }}" required>
                        <option value="">Pilih klien</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->company }} · {{ $client->name }} (#{{ $client->id }}) @if($client->package_name) · {{ $client->package_name }} @endif @if($client->subscription_end) · s.d. {{ $client->subscription_end->format('d/m/Y') }} @endif</option>
                        @endforeach
                    </select>
                    <label for="u-{{ $school->id }}">Masa aktif portal sampai</label>
                    <input class="form-control mb-3" id="u-{{ $school->id }}" type="date" name="until_date" min="{{ now('Asia/Jakarta')->toDateString() }}" required>
                    <fieldset><legend class="h6">Modul sesuai pesanan</legend>
                        @foreach($modules as $key => $label)
                            <label class="mr-3"><input type="checkbox" name="modules[]" value="{{ $key }}"> {{ $label }}</label>
                        @endforeach
                    </fieldset>
                    <button class="btn btn-primary mt-2" @disabled($clients->isEmpty())>Konfirmasi dan aktifkan sekolah</button>
                </form>
            </details>
        @empty
            <p class="text-muted mb-0">Belum ada sekolah yang menunggu verifikasi.</p>
        @endforelse
        {{ $pending->withQueryString()->links() }}
    </div></div>
    <div class="card"><div class="card-body">
        <h2 class="h5">Riwayat konfirmasi melalui SISCOM</h2>
        <div class="table-responsive"><table class="table"><thead><tr><th>Kode sekolah</th><th>Klien</th><th>Modul disetujui</th><th>Masa aktif disetujui</th><th>Dikonfirmasi oleh</th><th>Waktu</th></tr></thead><tbody>
            @forelse($history as $item)
                <tr><td>{{ strtoupper($item->school_code) }}</td><td>{{ $item->client }}</td><td>{{ implode(', ', array_intersect_key($modules, array_flip(explode(',', $item->modules)))) }}</td><td>{{ $item->until_date }}</td><td>{{ $item->operator ?? 'Akun sudah dihapus' }}</td><td>{{ $item->approved_at }}</td></tr>
            @empty
                <tr><td colspan="6">Belum ada konfirmasi melalui SISCOM.</td></tr>
            @endforelse
        </tbody></table></div>
        {{ $history->withQueryString()->links() }}
    </div></div>
@endif
@endsection
