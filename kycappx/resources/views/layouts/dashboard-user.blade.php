@php
    $navigation = [
        ['label' => 'Overview', 'route' => 'dashboard', 'pattern' => 'dashboard', 'icon' => 'squares-2x2'],
        ['label' => 'Wallet', 'route' => 'wallet', 'pattern' => 'wallet*', 'icon' => 'credit-card'],
        ['label' => 'Transactions', 'route' => 'transactions', 'pattern' => 'transactions', 'icon' => 'banknotes'],
        ['label' => 'Verifications', 'route' => 'verifications.index', 'pattern' => 'verifications.*', 'icon' => 'shield-check'],
        ['label' => 'API Keys', 'route' => 'api.keys', 'pattern' => 'api.keys*', 'icon' => 'key'],
        ['label' => 'Profile', 'route' => 'profile.edit', 'pattern' => 'profile.*', 'icon' => 'cog-6-tooth'],
    ];
    $roleNames = auth()->user()->getRoleNames();
    $roleLabel = $roleNames->isNotEmpty()
        ? $roleNames->map(fn ($role) => \Illuminate\Support\Str::headline($role))->implode(' / ')
        : 'Customer';
    $serviceLinks = $workspaceServices ?? collect();
@endphp

<x-layouts.app :title="$title ?? 'Customer Workspace'">
    <div x-data="{ mobileNav: false }" class="min-h-screen px-3 py-3 sm:px-5 lg:px-6">
        <div class="mx-auto grid max-w-[1660px] gap-4 xl:grid-cols-[18rem,minmax(0,1fr)]">
            <aside class="shell-sidebar hidden min-h-[calc(100vh-1.5rem)] rounded-[2rem] border border-white/8 p-5 shadow-[0_30px_80px_rgba(2,6,23,0.4)] xl:flex xl:flex-col">
                <div class="flex items-center gap-3">
                    <x-application-logo />
                    <div>
                        <div class="text-base font-semibold">{{ $siteSettings->site_name ?? config('app.name') }}</div>
                        <div class="text-sm text-white/60">Customer workspace</div>
                    </div>
                </div>

                <div class="mt-6 rounded-[1.6rem] border border-white/10 bg-white/6 p-4 backdrop-blur">
                    <div class="text-sm font-semibold text-white">{{ auth()->user()->name }}</div>
                    <div class="mt-1 text-sm text-white/60">{{ auth()->user()->email }}</div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <span class="badge-soft border-white/10 bg-white/10 text-white/85">{{ $roleLabel }}</span>
                        @if (auth()->user()->isUserPro())
                            <span class="badge-soft border-teal-400/30 bg-teal-400/15 text-teal-100">Discounted</span>
                        @endif
                    </div>
                </div>

                <div class="mt-6">
                    <div class="px-3 text-xs font-semibold uppercase tracking-[0.28em] text-white/40">Workspace</div>
                    <nav class="mt-3 space-y-1.5">
                        @foreach ($navigation as $item)
                            <a
                                href="{{ route($item['route']) }}"
                                @class([
                                    'sidebar-link',
                                    'sidebar-link--active' => request()->routeIs($item['pattern']),
                                ])
                            >
                                <flux:icon :icon="$item['icon']" variant="mini" />
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </nav>
                </div>

                <div class="mt-6">
                    <div class="px-3 text-xs font-semibold uppercase tracking-[0.28em] text-white/40">Services</div>
                    <nav class="mt-3 space-y-1.5">
                        @foreach ($serviceLinks as $service)
                            <a
                                href="{{ route('verifications.create', ['service' => $service->id]) }}"
                                @class([
                                    'sidebar-link',
                                    'sidebar-link--active' => request()->routeIs('verifications.create') && (int) request()->query('service') === $service->id,
                                    'opacity-60' => ! $service->is_active,
                                ])
                            >
                                <flux:icon :icon="match (strtoupper($service->code)) {
                                    'BVN' => 'identification',
                                    'NIN' => 'shield-check',
                                    'CAC' => 'building-office',
                                    'TIN' => 'receipt-percent',
                                    'PHONE' => 'device-phone-mobile',
                                    'ACCOUNT' => 'banknotes',
                                    default => 'sparkles',
                                }" variant="mini" />
                                <span>{{ strtoupper($service->code) }}</span>
                                @unless ($service->is_active)
                                    <span class="ms-auto text-[10px] uppercase tracking-[0.2em] text-white/40">Soon</span>
                                @endunless
                            </a>
                        @endforeach
                    </nav>
                </div>

                @if (auth()->user()->isAdmin())
                    <div class="mt-6">
                        <div class="px-3 text-xs font-semibold uppercase tracking-[0.28em] text-white/40">Operations</div>
                        <div class="mt-3">
                            <a href="{{ route('admin.dashboard') }}" class="sidebar-link">
                                <flux:icon icon="command-line" variant="mini" />
                                <span>Admin dashboard</span>
                            </a>
                        </div>
                    </div>
                @endif

                <div class="mt-auto space-y-3 pt-6">
                    <x-ui.theme-toggle class="w-full justify-center border-white/10 bg-white/6 text-white" />

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <flux:button type="submit" variant="ghost" class="w-full justify-center text-white hover:bg-white/10">
                            <flux:icon icon="arrow-left-start-on-rectangle" variant="mini" />
                            <span>Sign out</span>
                        </flux:button>
                    </form>
                </div>
            </aside>

            <div class="min-w-0 space-y-4">
                <header class="shell-header sticky top-3 z-40 rounded-[1.75rem] border px-4 py-4 shadow-[0_20px_50px_rgba(15,23,42,0.08)] backdrop-blur-xl sm:px-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="flex items-start gap-3">
                            <flux:button variant="ghost" class="xl:hidden" x-on:click="mobileNav = true">
                                <flux:icon icon="bars-3" variant="mini" />
                            </flux:button>

                            <div>
                                <div class="flex flex-wrap items-center gap-3">
                                    <a href="/" class="logo-placeholder text-sm">
                                        <x-application-logo />
                                        <span>{{ $siteSettings->site_name ?? config('app.name') }}</span>
                                    </a>
                                    <span class="service-chip">{{ $roleLabel }}</span>
                                    @if ($siteSettings->header_notice)
                                        <span class="hidden text-xs text-slate-500 dark:text-slate-300 md:inline">{{ $siteSettings->header_notice }}</span>
                                    @endif
                                </div>

                                <p class="section-kicker mt-4">{{ $header ?? 'Operations overview' }}</p>
                                <!--h1 class="mt-2 text-2xl font-semibold text-slate-950 text-balance dark:text-white sm:text-3xl">{{ $header ?? 'Operations overview' }}</h1>
                                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                                    Wallet topups, dedicated account cards, verifications, API keys, and security settings from one responsive control surface.
                                </p-->
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('wallet') }}">
                                <flux:button variant="primary" color="teal" icon="plus-circle">Top up</flux:button>
                            </a>
                            <a href="{{ route('verifications.create') }}">
                                <flux:button variant="outline" icon="shield-check">Run service</flux:button>
                            </a>
                            <x-ui.theme-toggle class="hidden lg:inline-flex" />
                        </div>
                    </div>

                    <div class="mt-4 flex gap-2 overflow-x-auto pb-1 xl:hidden">
                        @foreach ($navigation as $item)
                            <a
                                href="{{ route($item['route']) }}"
                                @class([
                                    'service-chip whitespace-nowrap',
                                    'bg-slate-950 text-white dark:bg-white dark:text-slate-950' => request()->routeIs($item['pattern']),
                                ])
                            >
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                    </div>
                </header>

                <div
                    x-show="mobileNav"
                    x-cloak
                    class="fixed inset-0 z-50 bg-slate-950/45 p-3 backdrop-blur-sm xl:hidden"
                    x-on:click.self="mobileNav = false"
                >
                    <aside class="shell-mobile-panel flex h-full max-w-sm flex-col rounded-[2rem] border border-white/10 p-5 shadow-[0_24px_80px_rgba(2,6,23,0.45)]">
                        <div class="flex items-center justify-between">
                            <div class="logo-placeholder text-white">
                                <x-application-logo />
                                <span>{{ $siteSettings->site_name ?? config('app.name') }}</span>
                            </div>

                            <flux:button variant="ghost" class="text-white" x-on:click="mobileNav = false">
                                <flux:icon icon="x-mark" variant="mini" />
                            </flux:button>
                        </div>

                        <div class="mt-6 rounded-[1.6rem] border border-white/10 bg-white/6 p-4">
                            <div class="font-semibold">{{ auth()->user()->name }}</div>
                            <div class="mt-1 text-sm text-white/60">{{ auth()->user()->email }}</div>
                        </div>

                        <nav class="mt-6 space-y-1.5">
                            @foreach ($navigation as $item)
                                <a href="{{ route($item['route']) }}" class="sidebar-link" x-on:click="mobileNav = false">
                                    <flux:icon :icon="$item['icon']" variant="mini" />
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </nav>

                        <div class="mt-6">
                            <div class="px-3 text-xs font-semibold uppercase tracking-[0.28em] text-white/40">Services</div>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($serviceLinks as $service)
                                    <a href="{{ route('verifications.create', ['service' => $service->id]) }}" class="service-chip text-white/85">
                                        {{ strtoupper($service->code) }}
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-auto space-y-3">
                            <x-ui.theme-toggle class="w-full justify-center border-white/10 bg-white/6 text-white" />
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <flux:button type="submit" variant="ghost" class="w-full justify-center text-white hover:bg-white/10">
                                    Sign out
                                </flux:button>
                            </form>
                        </div>
                    </aside>
                </div>

                @if (session('status'))
                    <flux:callout variant="success" icon="check-circle">
                        <flux:callout.heading>Status updated</flux:callout.heading>
                        <flux:callout.text>{{ session('status') }}</flux:callout.text>
                    </flux:callout>
                @endif

                <main class="space-y-6">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </div>
</x-layouts.app>
