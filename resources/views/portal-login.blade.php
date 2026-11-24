<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="referrer" content="no-referrer"><title>Masuk portal sekolah · SISCOM</title></head>
<body><main style="max-width:36rem;margin:8vh auto;padding:1.5rem;font:1.05rem/1.6 system-ui;color:#163252">
<h1>Masuk portal sekolah</h1><p>Anda masuk sebagai <strong>{{ auth()->user()->email }}</strong>.</p>
@forelse($schools as $school)
    <form method="post" action="{{ route('portal-login.issue') }}">@csrf
        <input type="hidden" name="state" value="{{ session('portal_login_state') }}">
        <input type="hidden" name="school_id" value="{{ $school->id }}">
        <p><strong>{{ $school->name }}</strong><br>NPSN {{ $school->npsn }} · Kode {{ strtoupper($school->code) }}</p>
        <button type="submit">Masuk ke {{ $school->name }}</button>
    </form>
@empty
    <p>Belum ada sekolah aktif yang terhubung ke akun ini. Hubungi Super Admin SISCOM dengan menyertakan NPSN sekolah dan email akun Anda. Anda tidak perlu mendaftar ulang.</p>
@endforelse
<p><a href="{{ route('client.dashboard') }}">Kembali ke SISCOM</a></p></main></body></html>
