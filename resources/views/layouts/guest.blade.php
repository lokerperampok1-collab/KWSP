<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Laravel'))</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <link rel="stylesheet" href="{{ asset('css/design_tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('myasset/css/auth_v2.css') }}">
    <link rel="stylesheet" href="{{ asset('custom_ui.css') }}">

    <style>
        body{font-family:'Poppins', 'Plus Jakarta Sans', sans-serif}
    </style>
</head>
<body class="corporate-light">
    @include('partials.animated-bg')
    <main class="gmt-auth">
        {{ $slot }}
    </main>

    @stack('scripts')
</body>
</html>
