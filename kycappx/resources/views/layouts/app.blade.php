@php
    $themePreference = auth()->user()?->theme_preference ?? ($siteSettings->default_theme ?? 'system');
@endphp

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" data-theme="{{ $themePreference }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $siteSettings->site_tagline ?? 'Kycappx helps teams run wallet operations, verification workflows, and API access from one clean control surface.' }}">
    <title>{{ isset($title) ? $title.' | '.($siteSettings->site_name ?? config('app.name')) : ($siteSettings->site_name ?? config('app.name')) }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=sora:400,500,600,700|jetbrains-mono:400,500,600&display=swap" rel="stylesheet" />
    <script>
        window.__theme = {
            initial: @js($themePreference),
            enabled: @js($siteSettings->dark_mode_enabled ?? true),
            persistUrl: @js(auth()->check() ? route('profile.theme') : null),
            csrfToken: @js(csrf_token()),
        };

        (() => {
            const settings = window.__theme;
            const stored = window.localStorage.getItem('kycappx.theme');
            const theme = settings.enabled === false
                ? 'light'
                : (settings.persistUrl ? settings.initial : (stored || settings.initial || 'system'));
            const isDark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);

            document.documentElement.classList.toggle('dark', isDark);
            document.documentElement.dataset.theme = theme;

            if (settings.enabled !== false) {
                window.localStorage.setItem('kycappx.theme', theme);
            }
        })();
    </script>

    @vite(['resources/css/app.css','resources/js/app.js'])
    @fluxAppearance
    @livewireStyles
</head>
<body class="min-h-screen antialiased transition-colors duration-300">
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute inset-0 grid-pattern opacity-30"></div>
        <div class="absolute inset-x-0 top-0 h-72 bg-slate-950/6 dark:bg-white/3"></div>
        <div class="absolute -left-14 top-24 h-72 w-72 rounded-full bg-teal-500/18 blur-3xl dark:bg-teal-400/10"></div>
        <div class="absolute right-0 top-16 h-80 w-80 rounded-full bg-amber-300/22 blur-3xl dark:bg-amber-300/10"></div>
        <div class="absolute bottom-0 left-1/3 h-80 w-80 rounded-full bg-sky-300/12 blur-3xl dark:bg-sky-500/8"></div>
    </div>

    <div class="shell min-h-screen">
        {{ $slot }}
    </div>

    @fluxScripts
    @livewireScripts
</body>
</html>
