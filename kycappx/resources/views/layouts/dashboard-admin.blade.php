@php
    $navigation = [
        ['label' => 'Overview', 'route' => 'admin.dashboard', 'pattern' => 'admin.dashboard', 'icon' => 'chart-bar'],
        ['label' => 'Users', 'route' => 'admin.users.index', 'pattern' => ['admin.users.*', 'admin.customers.*'], 'icon' => 'users'],
        ['label' => 'Services', 'route' => 'admin.services.index', 'pattern' => 'admin.services.*', 'icon' => 'shield-check'],
        ['label' => 'Providers', 'route' => 'admin.providers.index', 'pattern' => 'admin.providers.*', 'icon' => 'cpu-chip'],
        ['label' => 'Site Settings', 'route' => 'admin.settings.site', 'pattern' => 'admin.settings.*', 'icon' => 'cog-8-tooth'],
        ['label' => 'Webhook Logs', 'route' => 'admin.logs.webhooks', 'pattern' => 'admin.logs.webhooks', 'icon' => 'arrow-path-rounded-square'],
        ['label' => 'Verification Logs', 'route' => 'admin.logs.verifications', 'pattern' => 'admin.logs.verifications', 'icon' => 'document-magnifying-glass'],
        ['label' => 'Audit Logs', 'route' => 'admin.logs.audit', 'pattern' => 'admin.logs.audit', 'icon' => 'clipboard-document-list'],
    ];
@endphp

<x-layouts.app :title="$title ?? 'Admin Workspace'">
    <div x-data="{ mobileNav: false }" class="min-h-screen px-3 py-3 sm:px-5 lg:px-6">
        <div class="mx-auto grid max-w-[1660px] gap-4 xl:grid-cols-[18rem,minmax(0,1fr)]">
            <aside class="shell-sidebar hidden min-h-[calc(100vh-1.5rem)] rounded-[2rem] border border-white/8 p-5 shadow-[0_30px_80px_rgba(2,6,23,0.45)] xl:flex xl:flex-col">
                <div class="flex items-center gap-3">
                    <x-application-logo />
                    <div>
                        <div class="text-base font-semibold">{{ $siteSettings->site_name ?? config('app.name') }}</div>
                        <div class="text-sm text-white/60">Operations cockpit</div>
                    </div>
                </div>

                <div class="mt-6 rounded-[1.6rem] border border-white/10 bg-white/6 p-4 backdrop-blur">
                    <div class="text-xs uppercase tracking-[0.24em] text-white/40">Signed in as</div>
                    <div class="mt-3 text-lg font-semibold text-white">{{ auth()->user()->name }}</div>
                    <div class="mt-1 text-sm text-white/60">{{ auth()->user()->email }}</div>
                </div>

                <nav class="mt-6 space-y-1.5">
                    @foreach ($navigation as $item)
                        <a
                            href="{{ route($item['route']) }}"
                            @class([
                                'sidebar-link',
                                'sidebar-link--active' => request()->routeIs(...(array) $item['pattern']),
                            ])
                        >
                            <flux:icon :icon="$item['icon']" variant="mini" />
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </nav>

                <div class="mt-auto space-y-3 pt-6">
                    <a href="{{ route('dashboard') }}" class="block">
                        <flux:button variant="ghost" class="w-full justify-center text-white hover:bg-white/10">
                            <flux:icon icon="arrow-right" variant="mini" />
                            <span>Customer workspace</span>
                        </flux:button>
                    </a>

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
                                    <span class="service-chip">Admin</span>
                                </div>

                                <p class="section-kicker mt-4">{{ $header ?? 'Operations overview' }}</p>
                                <!--h1 class="mt-2 text-2xl font-semibold text-slate-950 text-balance dark:text-white sm:text-3xl">{{ $header ?? 'Operations overview' }}</h1-->
                                <!--p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                                    Manage users, pricing, funding providers, DVA switches, service availability, and audit visibility from one control plane.
                                </p-->
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('admin.settings.site') }}">
                                <flux:button variant="outline" icon="cog-6-tooth">Site settings</flux:button>
                            </a>
                            <a href="{{ route('dashboard') }}">
                                <flux:button variant="primary" color="teal" icon="window">Customer view</flux:button>
                            </a>
                        </div>
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

                        <nav class="mt-6 space-y-1.5">
                            @foreach ($navigation as $item)
                                <a href="{{ route($item['route']) }}" class="sidebar-link" x-on:click="mobileNav = false">
                                    <flux:icon :icon="$item['icon']" variant="mini" />
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </nav>

                        <div class="mt-auto space-y-3">
                            <a href="{{ route('dashboard') }}" class="block">
                                <flux:button variant="ghost" class="w-full justify-center text-white hover:bg-white/10">
                                    Customer workspace
                                </flux:button>
                            </a>
                        </div>
                    </aside>
                </div>

                @if (session('status'))
                    <flux:callout variant="success" icon="check-circle">
                        <flux:callout.heading>Settings updated</flux:callout.heading>
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
