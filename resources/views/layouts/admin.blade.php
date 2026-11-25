@extends('adminlte::page')

@section('title', 'SIS.COM | ' . trim($__env->yieldContent('page-title', 'Dashboard')))

@section('content_header')
    @include('layouts.partials.portal-header', [
        'portal' => 'admin',
        'notificationCount' => $adminNotificationCount ?? 0,
        'notificationRoute' => route('admin.notifications'),
    ])
@stop

@section('content')
    @include('layouts.partials.flash-messages')

    <main class="sis-page-content" id="main-content">
        @yield('admin-content')
    </main>

    <script src="{{ asset('js/password-visibility.js') }}?v=1" defer></script>
    <script src="{{ asset('js/siscom-ui.js') }}?v=20261116" defer></script>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/siscom-theme.css') }}?v=20261116">
@stop
