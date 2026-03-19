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
    <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,600,700|jetbrains-mono:400,500&display=swap" rel="stylesheet" />
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
    @livewireStyles
</head>
<body class="min-h-screen antialiased transition-colors duration-300">
    <div class="pointer-events-none fixed inset-0 -z-10">
        <div class="absolute inset-x-0 top-0 h-56 bg-slate-950 dark:bg-slate-900"></div>
        <div class="absolute left-0 top-16 h-72 w-72 rounded-full bg-teal-400/20 blur-3xl dark:bg-teal-400/10"></div>
        <div class="absolute right-0 top-24 h-80 w-80 rounded-full bg-amber-300/25 blur-3xl dark:bg-amber-300/10"></div>
    </div>

    <div class="min-h-screen">
        <x-ui.theme-toggle class="fixed right-4 top-4 z-50 sm:right-6 sm:top-6" />
        {{ $slot }}
    </div>

    @livewireScripts
</body>
</html>
