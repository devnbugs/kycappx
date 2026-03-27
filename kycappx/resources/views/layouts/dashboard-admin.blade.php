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
    <div
        x-data="createDashboardShell('admin')"
        x-init="init()"
        x-on:keydown.escape.window="closeMobileNav()"
        class="min-h-screen px-3 py-3 sm:px-5 lg:px-6"
    >
        <div
            class="dashboard-shell mx-auto grid max-w-[1660px] gap-4"
            x-bind:style="desktopSidebarVisible ? '--dashboard-sidebar-width: 18rem;' : '--dashboard-sidebar-width: 0rem;'"
        >
            <aside
                x-show="desktopSidebarVisible"
                x-transition.opacity.duration.200ms
                class="dashboard-sidebar shell-sidebar hidden rounded-[2rem] border border-white/8 p-5 shadow-[0_30px_80px_rgba(2,6,23,0.45)] lg:flex lg:flex-col"
            >
                <div class="flex items-center gap-3">
                    <x-application-logo />
                    <div class="min-w-0">
                        <div class="truncate text-base font-semibold">{{ $siteSettings->site_name ?? config('app.name') }}</div>
                        <div class="text-sm text-white/60">Operations cockpit</div>
                    </div>
                </div>

                <div class="mt-6 rounded-[1.6rem] border border-white/10 bg-white/6 p-4 backdrop-blur">
                    <div class="text-xs uppercase tracking-[0.24em] text-white/40">Signed in as</div>
                    <div class="mt-3 text-lg font-semibold text-white">{{ auth()->user()->name }}</div>
                    <div class="mt-1 safe-wrap text-sm text-white/60">{{ auth()->user()->email }}</div>
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
                    <flux:button href="{{ route('dashboard') }}" variant="ghost" class="w-full justify-center text-white hover:bg-white/10">
                        <flux:icon icon="arrow-right" variant="mini" />
                        <span>Customer workspace</span>
                    </flux:button>

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
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start gap-3">
                                <flux:button
                                    variant="ghost"
                                    class="shrink-0"
                                    x-on:click="toggleSidebar()"
                                    x-bind:aria-expanded="(isDesktop() ? desktopSidebarVisible : mobileNav).toString()"
                                    aria-label="Toggle navigation"
                                >
                                    <flux:icon icon="bars-3" variant="mini" />
                                    <span x-text="isDesktop() ? (desktopSidebarVisible ? 'Hide sidebar' : 'Show sidebar') : 'Menu'"></span>
                                </flux:button>

                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <a href="{{ route('home') }}" class="logo-placeholder text-sm">
                                            <x-application-logo />
                                            <span>{{ $siteSettings->site_name ?? config('app.name') }}</span>
                                        </a>
                                        <span class="service-chip">Admin</span>
                                        @if ($siteSettings->header_notice)
                                            <span class="text-xs text-slate-500 dark:text-slate-300">{{ $siteSettings->header_notice }}</span>
                                        @endif
                                    </div>

                                    <p class="section-kicker mt-4">{{ $header ?? 'Page' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <flux:button href="{{ route('admin.settings.site') }}" variant="outline" icon="cog-6-tooth">Site settings</flux:button>
                            <flux:button href="{{ route('dashboard') }}" variant="primary" color="teal" icon="window">Customer view</flux:button>
                        </div>
                    </div>

                    <nav class="shell-header-nav mt-4" aria-label="Admin navigation">
                        @foreach ($navigation as $item)
                            <a
                                href="{{ route($item['route']) }}"
                                @class([
                                    'shell-header-link',
                                    'shell-header-link--active' => request()->routeIs(...(array) $item['pattern']),
                                ])
                            >
                                <flux:icon :icon="$item['icon']" variant="mini" />
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </nav>
                </header>

                <div
                    x-show="mobileNav"
                    x-cloak
                    x-transition.opacity
                    class="fixed inset-0 z-50 bg-slate-950/45 p-3 backdrop-blur-sm lg:hidden"
                    x-on:click.self="closeMobileNav()"
                >
                    <aside
                        class="shell-mobile-panel flex h-full w-full max-w-sm flex-col rounded-[2rem] border border-white/10 p-5 shadow-[0_24px_80px_rgba(2,6,23,0.45)]"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="-translate-x-4 opacity-0"
                        x-transition:enter-end="translate-x-0 opacity-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="translate-x-0 opacity-100"
                        x-transition:leave-end="-translate-x-4 opacity-0"
                    >
                        <div class="flex items-center justify-between gap-4">
                            <div class="logo-placeholder text-white">
                                <x-application-logo />
                                <span>{{ $siteSettings->site_name ?? config('app.name') }}</span>
                            </div>

                            <flux:button variant="ghost" class="text-white" x-on:click="closeMobileNav()">
                                <flux:icon icon="x-mark" variant="mini" />
                            </flux:button>
                        </div>

                        <nav class="mt-6 space-y-1.5">
                            @foreach ($navigation as $item)
                                <a
                                    href="{{ route($item['route']) }}"
                                    @class([
                                        'sidebar-link',
                                        'sidebar-link--active' => request()->routeIs(...(array) $item['pattern']),
                                    ])
                                    x-on:click="closeMobileNav()"
                                >
                                    <flux:icon :icon="$item['icon']" variant="mini" />
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </nav>

                        <div class="mt-auto space-y-3">
                            <flux:button href="{{ route('dashboard') }}" variant="ghost" class="w-full justify-center text-white hover:bg-white/10">
                                <span>Customer workspace</span>
                            </flux:button>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <flux:button type="submit" variant="ghost" class="w-full justify-center text-white hover:bg-white/10">
                                    <flux:icon icon="arrow-left-start-on-rectangle" variant="mini" />
                                    <span>Sign out</span>
                                </flux:button>
                            </form>
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
