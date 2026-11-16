<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#071a3d">

    <title>{{ config('app.name', 'SIS.COM') }} | Masuk</title>
    <link rel="icon" type="image/png" href="{{ asset('siscombg2ic.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: Inter, "Segoe UI", Arial, sans-serif; }
        .sis-login-bg {
            background:
                radial-gradient(circle at 15% 15%, rgba(14,165,233,.3), transparent 30%),
                radial-gradient(circle at 85% 80%, rgba(37,99,235,.26), transparent 34%),
                linear-gradient(135deg, #06142f 0%, #0b2b66 52%, #071a3d 100%);
        }
    </style>
</head>
<body class="sis-login-bg min-h-screen antialiased">
    {{ $slot }}
</body>
</html>
