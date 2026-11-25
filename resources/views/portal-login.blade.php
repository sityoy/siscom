<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <title>Pilih sekolah · SIS.COM</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/portal-access.css') }}?v=1">
</head>
<body>
<header class="access-header">
    <div class="access-wrap">
        <a class="access-brand" href="{{ route('client.dashboard') }}">
            <img src="{{ asset('siscombg2ic.png') }}" width="42" height="42" alt="">
            <span>SIS.COM</span>
        </a>
        <a class="access-link" href="{{ route('client.dashboard') }}">Kembali ke SIS.COM</a>
    </div>
</header>
<main class="access-wrap">
    <div class="access-shell">
        <section class="access-aside" aria-labelledby="portal-title">
            <p class="access-kicker">Portal Sekolah</p>
            <h2 id="portal-title">Ruang kerja sekolah Anda.</h2>
            <p>Buka ruang kerja untuk mengelola kegiatan dan administrasi sekolah sesuai layanan yang aktif.</p>
            <img class="access-art" src="{{ asset('portal/portal-3d.webp') }}" alt="">
            <div class="access-account">
                <span>Akun SIS.COM</span>
                <strong>{{ auth()->user()->email }}</strong>
            </div>
        </section>
        <section class="access-content" aria-labelledby="school-title">
            <h1 id="school-title">Pilih sekolah</h1>
            <p class="access-intro">Pilih sekolah yang ingin Anda kelola dengan akun ini.</p>
            <div class="school-options">
                @forelse($schools as $school)
                    <form method="post" action="{{ route('portal-login.issue') }}" class="school-option">
                        @csrf
                        <input type="hidden" name="state" value="{{ session('portal_login_state') }}">
                        <input type="hidden" name="school_id" value="{{ $school->id }}">
                        <h2>{{ $school->name }}</h2>
                        <dl>
                            @if($school->npsn)
                                <div><dt>NPSN</dt><dd>{{ $school->npsn }}</dd></div>
                            @endif
                            <div><dt>Kode SIS</dt><dd>{{ strtoupper($school->code) }}</dd></div>
                        </dl>
                        <button type="submit" aria-label="Masuk ke {{ $school->name }}">Masuk ke portal <span class="access-chevron" aria-hidden="true"></span></button>
                    </form>
                @empty
                    <div class="school-option">
                        <h2>Belum ada sekolah aktif</h2>
                        <p>Hubungi Super Admin SIS.COM agar akses sekolah ditautkan. Siapkan NPSN sekolah dan email akun di atas.</p>
                    </div>
                @endforelse
            </div>
            <p class="access-back"><a class="access-link" href="https://sityoyitsolutions.com/portal#masuk">Kembali ke halaman masuk Portal</a></p>
        </section>
    </div>
</main>
</body>
</html>
