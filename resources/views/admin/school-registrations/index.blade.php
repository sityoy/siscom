@extends('layouts.admin')
@section('page-title', 'Pendaftaran Sekolah')
@section('admin-content')
<h1 class="h3">Pendaftaran Sekolah</h1>
<a class="btn btn-outline-primary mb-3" href="{{ route('school-registrations.proposal') }}">Buat proposal paket</a>
@if($unavailable ?? false)
    <div class="alert alert-warning">Daftar belum dapat dibuka. Periksa migrasi SISCOM dan akses baca ke db_portalsiscom.schools.</div>
@else
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif
    <section class="card" id="hubungkan"><div class="card-body">
        <h2 class="h5">Hubungkan pengguna SISCOM ke sekolah</h2>
        <p>Pilih penanggung jawab yang berhak mendapatkan akses Kepala Sekolah. Cocokkan NPSN dengan dokumen sekolah. Jika sekolah sudah punya kode portal, masukkan kode tersebut untuk mempertahankan data dan masa aktifnya.</p>
        <form method="post" action="{{ route('school-registrations.link') }}">@csrf
            <label for="owner">Pengguna SISCOM</label>
            <select class="form-control mb-2" id="owner" name="user_id" required>
                <option value="">Pilih pengguna klien</option>
                @foreach($users as $owner)
                    <option value="{{ $owner->id }}" @selected((string) old('user_id', request('pengguna')) === (string) $owner->id)>{{ $owner->name }} · {{ $owner->email }}</option>
                @endforeach
            </select>
            <label>NPSN <input class="form-control" name="npsn" value="{{ old('npsn') }}" pattern="[A-Za-z0-9]{8}" maxlength="8" required></label>
            <label>Kode portal aktif, jika sudah ada <input class="form-control" name="code" value="{{ old('code') }}" maxlength="32"></label>
            <fieldset><legend class="h6 mt-3">Untuk sekolah yang belum memiliki kode portal</legend>
                <label>Nama sekolah <input class="form-control" name="name" value="{{ old('name') }}" maxlength="160"></label>
                <label>Masa aktif sampai <input class="form-control" name="until_date" type="date" min="{{ now('Asia/Jakarta')->toDateString() }}"></label>
                <div>@foreach($modules as $key => $label)
                    <label class="mr-3"><input type="checkbox" name="modules[]" value="{{ $key }}"> {{ $label }}</label>
                @endforeach</div>
            </fieldset>
            <button class="btn btn-primary">Hubungkan pengguna</button>
        </form>
    </div></section>
    <details class="card p-3 mb-3"><summary>Tambahkan CBT atau modul pendamping untuk sekolah aktif</summary>
        <form class="mt-3" method="post" action="{{ route('school-registrations.modules') }}">@csrf
            <label>Kode sekolah <input class="form-control" name="code" maxlength="32" required></label>
            <label>NPSN <input class="form-control" name="npsn" pattern="[A-Za-z0-9]{8}" required></label>
            <fieldset><legend class="h6">Layanan tambahan</legend>@foreach($modules as $key=>$label)
                <label class="mr-3"><input type="checkbox" name="modules[]" value="{{ $key }}"> {{ $label }}</label>
            @endforeach</fieldset>
            <button class="btn btn-primary">Tambahkan modul</button>
        </form></details>
    <div class="card"><div class="card-body">
        <h2 class="h5">Menunggu verifikasi ({{ $pending->total() }})</h2>
        <p>Verifikasi NPSN dan pastikan pendaftar berwenang mewakili sekolah sebelum memberikan akses Kepala Sekolah.</p>
        @forelse($pending as $school)
            <details class="border rounded p-3 mb-2">
                <summary><strong>{{ $school->name }}</strong> · {{ strtoupper($school->code) }}</summary>
                <p class="mt-3">Kontak pendaftar: {{ $school->contact }} · Paket yang diminta: {{ ['cbt'=>'CBT saja','portal'=>'Portal Sekolah','lengkap'=>'Portal + CBT','pilihan'=>'CBT + modul pilihan'][$school->requested_package] ?? $school->requested_package }}</p>
                <form method="POST" action="{{ route('school-registrations.approve', $school->id) }}">
                    @csrf
                    <label>NPSN terverifikasi <input class="form-control" name="npsn" value="{{ $school->npsn }}" pattern="[A-Za-z0-9]{8}" maxlength="8" @readonly($school->npsn) required></label>
                    <label for="c-{{ $school->id }}">Klien SISCOM sesuai kontrak</label>
                    <select class="form-control mb-3" name="client_id" id="c-{{ $school->id }}">
                        <option value="">Sekolah baru · gunakan akun portal pendaftar</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->company }} · {{ $client->name }} (#{{ $client->id }}) @if($client->package_name) · {{ $client->package_name }} @endif @if($client->subscription_end) · s.d. {{ $client->subscription_end->format('d/m/Y') }} @endif</option>
                        @endforeach
                    </select>
                    <label for="p-{{ $school->id }}">Pengguna SISCOM jika sudah ada</label>
                    <select class="form-control mb-3" name="user_id" id="p-{{ $school->id }}">
                        <option value="">Gunakan akun klien yang dipilih atau akun portal pendaftar</option>
                        @foreach($users as $owner)
                            <option value="{{ $owner->id }}">{{ $owner->name }} · {{ $owner->email }}</option>
                        @endforeach
                    </select>
                    <label for="u-{{ $school->id }}">Masa aktif portal sampai</label>
                    <input class="form-control mb-3" id="u-{{ $school->id }}" type="date" name="until_date" min="{{ now('Asia/Jakarta')->toDateString() }}" required>
                    <fieldset><legend class="h6">Modul sesuai pesanan</legend>
                        @foreach($modules as $key => $label)
                            <label class="mr-3"><input type="checkbox" name="modules[]" value="{{ $key }}" @checked($school->requested_package === 'lengkap' || ($school->requested_package === 'portal' && $key !== 'cbt') || (in_array($school->requested_package,['cbt','pilihan'],true) && $key === 'cbt') || ($school->requested_package === 'pilihan' && in_array($key,explode(',', $school->requested_modules ?? ''),true)))> {{ $label }}</label>
                        @endforeach
                    </fieldset>
                    <button class="btn btn-primary mt-2">Konfirmasi dan aktifkan sekolah</button>
                </form>
            </details>
        @empty
            <p class="text-muted mb-0">Belum ada sekolah yang menunggu verifikasi.</p>
        @endforelse
        {{ $pending->withQueryString()->links() }}
    </div></div>
    <div class="card"><div class="card-body">
        <h2 class="h5">Riwayat konfirmasi melalui SISCOM</h2>
        <div class="table-responsive"><table class="table"><thead><tr><th>Kode sekolah</th><th>Klien</th><th>Modul aktif</th><th>Status CBT</th><th>Masa aktif disetujui</th><th>Dikonfirmasi oleh</th><th>Waktu</th></tr></thead><tbody>
            @forelse($history as $item)
                <tr><td>{{ strtoupper($item->school_code) }}</td><td>{{ $item->client }}</td><td>{{ implode(', ', array_intersect_key($modules, array_flip(explode(',', $item->current_modules ?? $item->modules)))) }}</td><td>@if(in_array('cbt', explode(',', $item->current_modules ?? ''), true)){{ ['ready'=>'Siap', 'legacy'=>'CBT lama', 'error'=>'Perlu diperiksa'][$item->cbt_status] ?? 'Menunggu penyiapan' }}@else — @endif</td><td>{{ $item->until_date }}</td><td>{{ $item->operator ?? 'Akun sudah dihapus' }}</td><td>{{ $item->approved_at }}</td></tr>
            @empty
                <tr><td colspan="7">Belum ada konfirmasi melalui SISCOM.</td></tr>
            @endforelse
        </tbody></table></div>
        {{ $history->withQueryString()->links() }}
    </div></div>
@endif
@endsection
