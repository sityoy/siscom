@php
    $isAdminPortal = $portal === 'admin';
    $role = auth()->user()?->roles?->first()?->name ?? ($isAdminPortal ? 'admin' : 'client');
    $roleLabel = match ($role) {
        'super_admin' => 'Super Admin',
        'admin' => 'Administrator',
        'client' => 'Client',
        default => ucwords(str_replace('_', ' ', $role)),
    };
@endphp

<div class="sis-page-header">
    <div class="sis-page-heading">
        <span class="sis-eyebrow">
            <i class="fas {{ $isAdminPortal ? 'fa-layer-group' : 'fa-user-shield' }}"></i>
            {{ $isAdminPortal ? 'Pusat Kendali Bisnis' : 'Portal Layanan Client' }}
        </span>
        <h1>@yield('page-title', 'Dashboard')</h1>
        <p>
            {{ $isAdminPortal
                ? 'Kelola client, pekerjaan, invoice, pembayaran, dan dokumen SPJ dalam satu tempat.'
                : 'Pantau project, tagihan, pembayaran, dokumen, dan dukungan secara langsung.' }}
        </p>
    </div>

    <div class="sis-header-actions">
        <div class="sis-user-summary">
            <span class="sis-user-avatar" aria-hidden="true">
                {{ strtoupper(mb_substr(auth()->user()?->name ?? 'S', 0, 1)) }}
            </span>
            <span class="sis-user-meta">
                <strong>{{ auth()->user()?->name ?? 'Pengguna' }}</strong>
                <small>{{ $roleLabel }} &bull; {{ now()->translatedFormat('d M Y') }}</small>
            </span>
        </div>

        <button type="button" class="btn sis-icon-button" data-theme-toggle
                aria-label="Ubah tema" title="Ubah tema terang/gelap">
            <i class="fas fa-moon" data-theme-icon></i>
        </button>

        <a href="{{ $notificationRoute }}" class="btn sis-icon-button position-relative"
           aria-label="Buka notifikasi" title="Notifikasi">
            <i class="fas fa-bell"></i>
            @if($notificationCount > 0)
                <span class="sis-notification-badge">
                    {{ $notificationCount > 99 ? '99+' : $notificationCount }}
                </span>
            @endif
        </a>
    </div>
</div>
