<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Kycappx') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,600,700|jetbrains-mono:400,500&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-slate-950 font-sans text-slate-100 antialiased">
        <div class="relative flex min-h-screen flex-col lg:flex-row">
            <div class="hidden lg:flex lg:w-[46%] p-6">
                <div class="glass-panel relative flex w-full flex-col justify-between overflow-hidden px-8 py-10">
                    <div class="absolute -right-16 top-10 h-48 w-48 rounded-full bg-amber-300/20 blur-3xl"></div>
                    <div class="absolute bottom-0 left-0 h-56 w-56 rounded-full bg-teal-400/20 blur-3xl"></div>

                    <div class="relative">
                        <a href="/" class="inline-flex items-center gap-3 text-sm font-semibold text-white/90">
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/10 text-lg shadow-lg">KX</span>
                            <span>{{ config('app.name', 'Kycappx') }}</span>
                        </a>

                        <div class="mt-16 max-w-lg">
                            <p class="section-kicker !text-teal-200">Operations Ready</p>
                            <h1 class="mt-4 text-5xl font-semibold leading-tight text-balance text-white">
                                Verification, wallet funding, and API control in one workspace.
                            </h1>
                            <p class="mt-5 max-w-md text-base leading-7 text-slate-200/80">
                                Ship a cleaner onboarding and compliance experience with a front office for customers and an operations cockpit for your team.
                            </p>
                        </div>
                    </div>

                    <div class="relative grid gap-4 sm:grid-cols-2">
                        <div class="rounded-[1.5rem] border border-white/10 bg-white/10 p-5">
                            <div class="text-sm text-white/60">Customer workspace</div>
                            <div class="mt-3 text-2xl font-semibold text-white">Wallets, keys, and verification history</div>
                        </div>
                        <div class="rounded-[1.5rem] border border-white/10 bg-white/10 p-5">
                            <div class="text-sm text-white/60">Operations visibility</div>
                            <div class="mt-3 text-2xl font-semibold text-white">Admin dashboards, service health, and webhook logs</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-1 items-center justify-center p-4 sm:p-8">
                <div class="w-full max-w-xl">
                    <a href="/" class="badge-soft mb-4 text-slate-700">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-950 text-[11px] font-bold text-white">KX</span>
                        Back to home
                    </a>

                    <div class="surface-card overflow-hidden p-6 sm:p-8">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
