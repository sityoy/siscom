<x-guest-layout>
    @php($forSchoolPortal = session()->has('portal_login_state'))
    <main class="min-h-screen flex items-center justify-center px-4 py-8 sm:px-6">
        <div class="w-full max-w-6xl overflow-hidden rounded-3xl bg-white shadow-2xl lg:grid lg:grid-cols-2">
            <section class="relative hidden min-h-[650px] overflow-hidden bg-slate-950 p-12 text-white lg:flex lg:flex-col lg:justify-between">
                <div class="absolute -right-24 -top-24 h-80 w-80 rounded-full bg-cyan-400/20 blur-3xl"></div>
                <div class="absolute -bottom-32 -left-20 h-96 w-96 rounded-full bg-blue-500/25 blur-3xl"></div>

                <div class="relative z-10">
                    <img src="{{ asset('siscombg2.png') }}" alt="Logo SIS.COM" class="mb-8 h-28 w-28 object-contain drop-shadow-2xl">
                    <p class="mb-3 text-xs font-bold uppercase tracking-[.28em] text-cyan-300">Sityoy IT Solutions</p>
                    <h1 class="max-w-md text-4xl font-extrabold leading-tight">
                        {{ $forSchoolPortal ? 'Lanjutkan ke Portal Sekolah.' : 'Satu pusat kendali untuk layanan dan administrasi bisnis.' }}
                    </h1>
                    <p class="mt-5 max-w-lg text-base leading-relaxed text-slate-300">
                        {{ $forSchoolPortal ? 'Masuk dengan akun klien SIS.COM, lalu pilih sekolah yang ingin Anda kelola.' : 'Kelola client, project, invoice, pembayaran, support, dan dokumen SPJ secara aman serta terintegrasi.' }}
                    </p>
                </div>

                @unless($forSchoolPortal)
                <div class="relative z-10 grid grid-cols-2 gap-4 text-sm">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur">
                        <span class="mb-2 block text-2xl">📊</span>
                        <strong class="block">Monitoring Real-time</strong>
                        <small class="text-slate-400">Data bisnis lebih mudah dipantau.</small>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur">
                        <span class="mb-2 block text-2xl">🛡️</span>
                        <strong class="block">Dokumen Terpusat</strong>
                        <small class="text-slate-400">Invoice dan SPJ lebih tertata.</small>
                    </div>
                </div>
                @endunless
            </section>

            <section class="flex min-h-[650px] items-center bg-white p-7 sm:p-12 lg:p-16">
                <div class="mx-auto w-full max-w-md">
                    <div class="mb-9 lg:hidden">
                        <img src="{{ asset('siscombg2.png') }}" alt="Logo SIS.COM" class="h-24 w-24 object-contain">
                    </div>

                    <div class="mb-8">
                        <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-bold uppercase tracking-wider text-blue-700">
                            {{ $forSchoolPortal ? 'Akses Portal Sekolah' : 'Portal Resmi SIS.COM' }}
                        </span>
                        <h2 class="mt-4 text-3xl font-extrabold text-slate-900">{{ $forSchoolPortal ? 'Masuk dengan SIS.COM' : 'Selamat datang kembali' }}</h2>
                        <p class="mt-2 text-sm text-slate-500">{{ $forSchoolPortal ? 'Gunakan email dan kata sandi akun klien yang ditautkan ke sekolah.' : 'Masuk menggunakan akun yang telah terdaftar.' }}</p>
                    </div>

                    <x-auth-session-status class="mb-5 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700" :status="session('status')" />

                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        <div>
                            <label for="email" class="mb-2 block text-sm font-bold text-slate-700">Alamat email</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}"
                                   autocomplete="username" autofocus required
                                   class="w-full rounded-xl border-slate-300 px-4 py-3.5 text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                   placeholder="nama@email.com">
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div>
                            <div class="mb-2 flex items-center justify-between">
                                <label for="password" class="text-sm font-bold text-slate-700">Password</label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="text-xs font-semibold text-blue-700 hover:text-blue-900">
                                        Lupa password?
                                    </a>
                                @endif
                            </div>
                            <div class="relative">
                                <input id="password" type="password" name="password" autocomplete="current-password" required
                                       class="w-full rounded-xl border-slate-300 px-4 py-3.5 text-slate-900 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                       placeholder="Masukkan password">
                            </div>
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <label class="flex items-center gap-2 text-sm text-slate-600">
                            <input type="checkbox" name="remember" class="rounded border-slate-300 text-blue-700 focus:ring-blue-500">
                            Ingat saya di perangkat ini
                        </label>

                        <button type="submit"
                                class="flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-blue-700 to-blue-600 px-5 py-4 font-bold text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:from-blue-800 hover:to-blue-700">
                            {{ $forSchoolPortal ? 'Masuk dan pilih sekolah' : 'Masuk ke Dashboard' }}
                        </button>
                    </form>

                    @if($forSchoolPortal)
                        <a href="https://sityoyitsolutions.com/portal#masuk" class="mt-5 inline-block text-sm font-semibold text-blue-700 hover:text-blue-900">Kembali ke halaman masuk Portal</a>
                    @endif

                    <div class="mt-8 border-t border-slate-200 pt-6 text-center text-xs text-slate-500">
                        <p class="font-semibold text-slate-700">siscom.sityoyitsolutions.com</p>
                        <p class="mt-1">&copy; {{ now()->year }} PT Sityoy IT Solutions &bull; Upgrade Your System</p>
                    </div>
                </div>
            </section>
        </div>
    </main>

</x-guest-layout>
