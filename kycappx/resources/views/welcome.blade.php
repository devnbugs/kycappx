@php
    $marketingNavigation = [
        ['label' => 'Home', 'href' => '#home'],
        ['label' => 'Platform', 'href' => '#platform'],
        ['label' => 'Capabilities', 'href' => '#capabilities'],
        ['label' => 'Launch', 'href' => '#launch'],
        ['label' => 'Contact', 'href' => '#contact'],
    ];
@endphp

<x-layouts.app :title="($siteSettings->site_name ?? config('app.name')).' | Verification Operations'">
    <div x-data="{ mobileNav: false }" class="px-4 py-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-6">
            <header id="home" class="surface-card sticky top-4 z-50 px-5 py-4 sm:px-6">
                <div class="flex items-center justify-between gap-4">
                    <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-950 text-lg font-bold text-white shadow-lg">
                            {{ $siteSettings->logo_text ?? 'O' }}
                        </div>
                        <div class="min-w-0">
                            <div class="truncate text-lg font-semibold text-slate-950 dark:text-slate-50">
                                {{ $siteSettings->site_name ?? config('app.name') }}
                            </div>
                            <div class="text-sm text-slate-500 dark:text-slate-400">Version 2026.02.1</div>
                        </div>
                    </a>

                    <nav class="hidden items-center gap-2 lg:flex" aria-label="Primary">
                        @foreach ($marketingNavigation as $item)
                            <a href="{{ $item['href'] }}" class="shell-header-link">{{ $item['label'] }}</a>
                        @endforeach
                    </nav>

                    <div class="hidden items-center gap-3 sm:flex">
                        @auth
                            <flux:button href="{{ route('dashboard') }}" variant="primary" color="teal">Dashboard</flux:button>
                        @else
                            @if (Route::has('register') && $siteSettings->registration_enabled)
                                <flux:button href="{{ route('register') }}" variant="outline">Register</flux:button>
                            @endif

                            <flux:button href="{{ route('login') }}" variant="primary" color="teal">Login</flux:button>
                        @endauth
                    </div>

                    <flux:button variant="ghost" class="lg:hidden" x-on:click="mobileNav = !mobileNav" aria-label="Toggle navigation">
                        <flux:icon icon="bars-3" variant="mini" />
                    </flux:button>
                </div>

                <div
                    x-show="mobileNav"
                    x-cloak
                    x-transition.opacity
                    class="mt-4 space-y-3 border-t border-slate-200/70 pt-4 dark:border-slate-700/70 lg:hidden"
                >
                    <nav class="grid gap-2" aria-label="Mobile primary">
                        @foreach ($marketingNavigation as $item)
                            <a href="{{ $item['href'] }}" class="shell-header-link justify-center" x-on:click="mobileNav = false">
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                    </nav>

                    <div class="grid gap-2">
                        @auth
                            <flux:button href="{{ route('dashboard') }}" variant="primary" color="teal">Dashboard</flux:button>
                        @else
                            @if (Route::has('register') && $siteSettings->registration_enabled)
                                <flux:button href="{{ route('register') }}" variant="outline">Register</flux:button>
                            @endif

                            <flux:button href="{{ route('login') }}" variant="primary" color="teal">Login</flux:button>
                        @endauth
                    </div>
                </div>
            </header>

            <section id="platform" class="grid gap-6 lg:grid-cols-[1.15fr,0.85fr]">
                <div class="surface-card relative overflow-hidden bg-slate-950 p-8 text-white sm:p-10">
                    <div class="absolute right-0 top-0 h-56 w-56 rounded-full bg-amber-300/20 blur-3xl"></div>
                    <div class="absolute bottom-0 left-0 h-64 w-64 rounded-full bg-teal-400/20 blur-3xl"></div>

                    <div class="relative">
                        <p class="section-kicker !text-teal-200">Beyond Identity</p>
                        <h1 class="mt-4 max-w-3xl text-4xl font-semibold leading-tight text-balance sm:text-5xl">
                            Speedo Boost Ai Powered KYC/KYB Core
                        </h1>

                        <div class="mt-6">
                            <flux:separator />
                        </div>

                        <div class="mt-6 flex flex-wrap items-center gap-3">
                            <span class="badge-soft">NIN/vNIN Services</span>
                            <span class="badge-soft">BVN Services</span>
                            <span class="badge-soft">TIN Services</span>
                            <span class="badge-soft">VIN Verification</span>
                            <span class="badge-soft">WAEC Results</span>
                            <span class="badge-soft">Address Intelligence</span>
                        </div>

                        <div class="mt-8 flex flex-wrap gap-3">
                            @auth
                                <flux:button href="{{ route('dashboard') }}" variant="primary" color="teal">Dashboard</flux:button>
                            @else
                                @if (Route::has('register') && $siteSettings->registration_enabled)
                                    <flux:button href="{{ route('register') }}" variant="primary" color="teal">Create Account</flux:button>
                                @endif

                                <flux:button href="{{ route('login') }}" variant="outline">Sign In</flux:button>
                            @endauth
                        </div>

                        <div class="mt-10 grid gap-4 sm:grid-cols-3">
                            <div class="rounded-[1.5rem] border border-white/10 bg-white/10 p-5">
                                <div class="text-sm text-white/60">KYC coverage</div>
                                <div class="mt-3 text-2xl font-semibold">Worldwide KYC services applied</div>
                            </div>
                            <div class="rounded-[1.5rem] border border-white/10 bg-white/10 p-5">
                                <div class="text-sm text-white/60">Payments</div>
                                <div class="mt-3 text-2xl font-semibold">99% on scaling transactions</div>
                            </div>
                            <div class="rounded-[1.5rem] border border-white/10 bg-white/10 p-5">
                                <div class="text-sm text-white/60">Security</div>
                                <div class="mt-3 text-2xl font-semibold">True privacy and safety</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid gap-4">
                    <div class="metric-card">
                        <p class="section-kicker">Partners</p>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            @foreach (['Prembly', 'Kora', 'Paystack', 'Cloudflare'] as $partner)
                                <div class="rounded-[1.25rem] border border-slate-200/80 bg-white/70 px-4 py-4 text-sm font-semibold text-slate-950 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-50">
                                    {{ $partner }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section id="capabilities" class="grid gap-4 lg:grid-cols-3">
                <div class="metric-card">
                    <div class="badge-soft">Customer workspace</div>
                    <h3 class="mt-4 text-2xl font-semibold text-slate-950 dark:text-slate-50">KYC strength, verifications, API keys, and wallet rails in one place.</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                        Users can save KYC bio once, prefill core checks, fund wallets, review transactions, and manage their own access posture.
                    </p>
                </div>

                <div class="metric-card">
                    <div class="badge-soft">Admin control</div>
                    <h3 class="mt-4 text-2xl font-semibold text-slate-950 dark:text-slate-50">Switch providers and services without redeploying the app.</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                        Priority, product toggles, KYC catalog activation, and feature switches live in the admin workspace instead of hardcoded release steps.
                    </p>
                </div>

                <div class="metric-card">
                    <div class="badge-soft">Trust layer</div>
                    <h3 class="mt-4 text-2xl font-semibold text-slate-950 dark:text-slate-50">Safer authentication and cleaner rendering on every device.</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                        Cloudflare Turnstile protects sign-in and registration, while the refreshed UI handles mobile, tablet, laptop, and wide desktop screens more gracefully.
                    </p>
                </div>
            </section>

            <section id="launch" class="surface-card p-8 sm:p-10">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="section-kicker">Ready to launch</p>
                        <h2 class="mt-3 text-3xl font-semibold text-slate-950 text-balance dark:text-slate-50">
                            Start with a customer-facing KYC and wallet experience, then scale into the admin control plane as traffic grows.
                        </h2>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        @auth
                            <flux:button href="{{ route('dashboard') }}" variant="primary" color="teal">Open Dashboard</flux:button>
                        @else
                            <flux:button href="{{ route('login') }}" variant="outline">Sign In</flux:button>

                            @if (Route::has('register') && $siteSettings->registration_enabled)
                                <flux:button href="{{ route('register') }}" variant="primary" color="teal">Create Account</flux:button>
                            @endif
                        @endauth
                    </div>
                </div>
            </section>

            <footer id="contact" class="surface-card px-5 py-5 sm:px-6">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <div class="text-sm font-semibold text-slate-950 dark:text-slate-50">{{ $siteSettings->site_name ?? config('app.name') }}</div>
                        <div class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            {{ $siteSettings->footer_text ?? 'Secure identity and wallet operations from one workspace.' }}
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-4 text-sm text-slate-500 dark:text-slate-400">
                        <span>{{ $siteSettings->support_email ?: 'hello@onetera.serv00.net' }}</span>
                        @if ($siteSettings->support_phone)
                            <span>{{ $siteSettings->support_phone }}</span>
                        @endif
                        <a href="{{ route('privacy-policy') }}" class="font-medium text-slate-700 hover:text-slate-950 dark:text-slate-300 dark:hover:text-white">Privacy Policy</a>
                        <a href="{{ route('terms-of-service') }}" class="font-medium text-slate-700 hover:text-slate-950 dark:text-slate-300 dark:hover:text-white">Terms</a>
                        <a href="{{ route('login') }}" class="font-medium text-slate-700 hover:text-slate-950 dark:text-slate-300 dark:hover:text-white">Login</a>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</x-layouts.app>
