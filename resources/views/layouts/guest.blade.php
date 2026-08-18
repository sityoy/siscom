<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="utf-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <meta name="csrf-token"
          content="{{ csrf_token() }}">

    <title>

        SIS.COM

    </title>

    <link rel="preconnect"
          href="https://fonts.googleapis.com">

    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
          rel="stylesheet">

    <link rel="icon"
          type="image/png"
          href="{{ asset('siscombg2.png') }}">

    @vite([

        'resources/css/app.css',
        'resources/js/app.js'

    ])

</head>

<body style="
    font-family: 'Poppins', sans-serif;
    background:
        linear-gradient(
            135deg,
            #0f172a,
            #1e3a8a
        );
">

    {{ $slot }}

</body>

</html>
