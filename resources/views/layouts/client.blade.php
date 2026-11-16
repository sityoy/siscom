@extends('adminlte::page')

@section('title', 'SIS.COM | ' . trim($__env->yieldContent('page-title', 'Portal Client')))

@section('content_header')
    @include('layouts.partials.portal-header', [
        'portal' => 'client',
        'notificationCount' => $clientNotificationCount ?? 0,
        'notificationRoute' => route('client.notifications'),
    ])
@stop

@section('content')
    @include('layouts.partials.flash-messages')

    <main class="sis-page-content" id="main-content">
        @yield('client-content')
    </main>

    <script src="{{ asset('js/siscom-ui.js') }}?v=20261116" defer></script>
@stop

@section('css')
    <link rel="stylesheet" href="{{ asset('css/siscom-theme.css') }}?v=20261116">
@stop
