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
    <div class="card border-primary" id="sekolah-terdaftar">
        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center">
            <h2 class="h5 mb-2">Sekolah terdaftar <span class="badge badge-light">{{ $registered->total() }}</span></h2>
            <form method="get" class="form-inline" action="{{ route('school-registrations.index') }}#sekolah-terdaftar">
                <label class="sr-only" for="cari-sekolah">Cari nama, NPSN, atau kode</label>
                <input class="form-control form-control-sm mr-2" id="cari-sekolah" name="cari" maxlength="80" value="{{ request('cari') }}" placeholder="Nama, NPSN, atau kode">
                <button class="btn btn-sm btn-outline-primary">Cari</button>
            </form>
        </div>
        <div class="card-body">
            <p class="text-muted">Gunakan daftar ini untuk menambah layanan atau menghapus centang modul yang ingin dinonaktifkan. Riwayat dan data sekolah tetap tersimpan.</p>
            @forelse($registered as $entry)
                @php
                    $current = explode(',', $entry->modules);
                    $active = $entry->active && $entry->until_date >= now('Asia/Jakarta')->toDateString();
                @endphp
                <details class="border rounded p-3 mb-3">
                    <summary class="font-weight-bold">{{ $entry->name }}
                        <span class="badge {{ $active ? 'badge-success' : 'badge-secondary' }}">{{ $active ? 'Aktif' : 'Tidak aktif' }}</span>
                        <small class="text-muted ml-2">{{ $entry->npsn ?: 'NPSN belum ada' }} · {{ strtoupper($entry->code) }}</small>
                    </summary>
                    <div class="mt-3">
                        @if($current === ['cbt'])
                            <p class="mb-2"><strong>Paket CBT Sekolah saja</strong> · <a href="https://cbt-sekolah.sityoyitsolutions.com/">Buka CBT Sekolah</a></p>
                        @endif
                        @forelse(array_intersect_key($modules, array_flip($current)) as $label)
                            <span class="badge badge-primary mb-1">{{ $label }}</span>
                        @empty
                            <span class="text-muted">Belum ada modul dipilih.</span>
                        @endforelse
                        <p class="small mt-2">Masa layanan hingga {{ $entry->until_date }}
                        @if(in_array('cbt', $current, true))
                            · CBT {{ ['ready'=>'siap', 'legacy'=>'lama', 'error'=>'perlu diperiksa'][$entry->cbt_status] ?? 'menunggu penyiapan' }}<br>
                            <strong>Tarif CBT</strong>
                            @if($entry->amount !== null)
                                Rp {{ number_format($entry->amount,0,',','.') }} · {{ $cbtBases[$entry->basis] }}
                                @if($entry->units) · {{ $entry->units }} {{ $entry->basis }} · Total Rp {{ number_format($entry->amount * $entry->units,0,',','.') }} @endif
                            @else
                                Belum dicatat
                            @endif
                        @endif
                        </p>
                        <form method="post" action="{{ route('school-registrations.services', $entry->id) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="current_modules" value="{{ $entry->modules }}">
                            <fieldset class="border rounded p-2 mb-3">
                                <legend class="h6">Kelola modul sekolah</legend>
                                @foreach($modules as $key => $label)
                                    <label class="mr-3"><input type="checkbox" name="modules[]" value="{{ $key }}" @checked(in_array($key,$current,true))> {{ $label }}</label>
                                @endforeach
                            </fieldset>
                            <fieldset class="border rounded p-2 mb-3">
                                <legend class="h6">Kesepakatan CBT</legend>
                                <div class="form-row">
                                    <label class="col-md">Dasar tarif<select class="form-control" name="basis">
                                        <option value="">Pilih dasar tarif</option>
                                        @foreach($cbtBases as $key=>$label)
                                            <option value="{{ $key }}" @selected($entry->basis === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select></label>
                                    <label class="col-md">Tarif (Rp)<input class="form-control" type="number" name="amount" min="0" max="9999999999999" value="{{ $entry->amount }}"></label>
                                    <label class="col-md">Jumlah siswa atau ujian<input class="form-control" type="number" name="units" min="1" max="100000" value="{{ $entry->units }}"></label>
                                </div>
                                <label class="d-block">Catatan kesepakatan <input class="form-control" name="note" maxlength="500" value="{{ $entry->note }}"></label>
                            </fieldset>
                            <button class="btn btn-primary">Simpan layanan sekolah</button>
                        </form>
                    </div>
                </details>
            @empty
                <p class="text-muted mb-0">Tidak ada sekolah yang cocok dengan pencarian.</p>
            @endforelse
            {{ $registered->withQueryString()->links() }}
        </div>
    </div>
    <div class="card"><div class="card-body">
        <h2 class="h5">Menunggu verifikasi ({{ $pending->total() }})</h2>
        <p>Verifikasi NPSN dan pastikan pendaftar berwenang mewakili sekolah sebelum memberikan akses Kepala Sekolah.</p>
        @forelse($pending as $school)
            <details class="border rounded p-3 mb-2">
                <summary><strong>{{ $school->name }}</strong> · {{ strtoupper($school->code) }}</summary>
                <p class="mt-3">Kontak pendaftar: {{ $school->contact }} · Paket yang diminta: {{ ['cbt'=>'CBT Sekolah saja','portal'=>'Portal Sekolah','lengkap'=>'Portal + CBT','pilihan'=>'CBT + modul pilihan'][$school->requested_package] ?? $school->requested_package }}</p>
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
                        @if($school->requested_package === 'cbt')
                            <input type="hidden" name="modules[]" value="cbt">
                            <p class="mb-1"><strong>CBT Sekolah saja</strong> · Layanan ujian berbasis komputer.</p>
                            <p class="small text-muted">Setelah ruang ujian siap, pengguna masuk melalui <a href="https://cbt-sekolah.sityoyitsolutions.com/">CBT Sekolah</a> dengan NPSN atau kode SIS.</p>
                        @else
                        @foreach($modules as $key => $label)
                            <label class="mr-3"><input type="checkbox" name="modules[]" value="{{ $key }}" @checked($school->requested_package === 'lengkap' || ($school->requested_package === 'portal' && $key !== 'cbt') || (in_array($school->requested_package,['cbt','pilihan'],true) && $key === 'cbt') || ($school->requested_package === 'pilihan' && in_array($key,explode(',', $school->requested_modules ?? ''),true)))> {{ $label }}</label>
                        @endforeach
                        @endif
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
