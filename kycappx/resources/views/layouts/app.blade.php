<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Kycappx helps teams run wallet operations, verification workflows, and API access from one clean control surface.">
    <title>{{ isset($title) ? $title.' | '.config('app.name') : config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,600,700|jetbrains-mono:400,500&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css','resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen text-slate-900 antialiased">
    <div class="pointer-events-none fixed inset-0 -z-10">
        <div class="absolute inset-x-0 top-0 h-56 bg-slate-950"></div>
        <div class="absolute left-0 top-16 h-72 w-72 rounded-full bg-teal-400/20 blur-3xl"></div>
        <div class="absolute right-0 top-24 h-80 w-80 rounded-full bg-amber-300/25 blur-3xl"></div>
    </div>

    <div class="min-h-screen">
        {{ $slot }}
    </div>

    @livewireScripts
</body>
</html>
