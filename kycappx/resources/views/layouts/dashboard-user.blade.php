@php
    $navigation = [
        ['label' => 'Overview', 'route' => 'dashboard', 'pattern' => 'dashboard', 'icon' => 'squares-2x2'],
        ['label' => 'Wallet', 'route' => 'wallet', 'pattern' => 'wallet*', 'icon' => 'credit-card'],
        ['label' => 'Transactions', 'route' => 'transactions', 'pattern' => 'transactions', 'icon' => 'banknotes'],
        ['label' => 'Verifications', 'route' => 'verifications.index', 'pattern' => 'verifications.*', 'icon' => 'shield-check'],
        ['label' => 'SMS', 'route' => 'sms.index', 'pattern' => 'sms.*', 'icon' => 'chat-bubble-left-right'],
        ['label' => 'KYC Strength', 'route' => 'kyc.edit', 'pattern' => 'kyc.*', 'icon' => 'identification'],
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
    <div
        x-data="createDashboardShell('customer')"
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
                class="dashboard-sidebar shell-sidebar hidden rounded-[2rem] border border-white/8 p-5 shadow-[0_30px_80px_rgba(2,6,23,0.4)] lg:flex lg:flex-col"
            >
                <div class="flex items-center gap-3">
                    <x-application-logo />
                    <div class="min-w-0">
                        <div class="truncate text-base font-semibold">{{ $siteSettings->site_name ?? config('app.name') }}</div>
                        <div class="text-sm text-white/60">Customer workspace</div>
                    </div>
                </div>

                <div class="mt-6 rounded-[1.6rem] border border-white/10 bg-white/6 p-4 backdrop-blur">
                    <div class="text-sm font-semibold text-white">{{ auth()->user()->name }}</div>
                    <div class="mt-1 safe-wrap text-sm text-white/60">{{ auth()->user()->email }}</div>
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
                        @forelse ($serviceLinks as $service)
                            @if (is_object($service) && isset($service->id))
                                <a
                                    href="{{ route('verifications.create', ['service' => $service->id]) }}"
                                    @class([
                                        'sidebar-link',
                                        'sidebar-link--active' => request()->routeIs('verifications.create') && (int) request()->query('service') === $service->id,
                                        'opacity-60' => ! $service->is_active,
                                    ])
                                >
                                    <flux:icon :icon="match (strtolower($service->type ?? '')) {
                                        'bank' => 'credit-card',
                                        'address' => 'map-pin',
                                        'kyb' => 'building-office',
                                        'vehicle' => 'truck',
                                        'education' => 'academic-cap',
                                        'compliance' => 'document-text',
                                        default => 'shield-check',
                                    }" variant="mini" />
                                    <span>{{ strtoupper($service->code) }}</span>
                                    @unless ($service->is_active)
                                        <span class="ms-auto text-[10px] uppercase tracking-[0.2em] text-white/40">Soon</span>
                                    @endunless
                                </a>
                            @endif
                        @empty
                            <div class="rounded-[1.5rem] border border-dashed border-white/10 px-4 py-4 text-sm text-white/60">
                                No verification services are active yet.
                            </div>
                        @endforelse
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
                                        <span class="service-chip">{{ $roleLabel }}</span>
                                        @if ($siteSettings->header_notice)
                                            <span class="text-xs text-slate-500 dark:text-slate-300">{{ $siteSettings->header_notice }}</span>
                                        @endif
                                    </div>

                                    <p class="section-kicker mt-4">{{ $header ?? 'Page' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <flux:button href="{{ route('wallet') }}" variant="primary" color="teal" icon="plus-circle">Top up</flux:button>
                            <flux:button href="{{ route('kyc.edit') }}" variant="outline" icon="identification">KYC</flux:button>
                            <flux:button href="{{ route('verifications.create') }}" variant="outline" icon="shield-check">Run Verification</flux:button>
                            <flux:button href="{{ route('sms.index') }}" variant="outline" icon="chat-bubble-left-right">SMS</flux:button>
                            <x-ui.theme-toggle class="hidden lg:inline-flex" />
                        </div>
                    </div>

                    <nav class="shell-header-nav mt-4" aria-label="Workspace navigation">
                        @foreach ($navigation as $item)
                            <a
                                href="{{ route($item['route']) }}"
                                @class([
                                    'shell-header-link',
                                    'shell-header-link--active' => request()->routeIs($item['pattern']),
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

                        <div class="mt-6 rounded-[1.6rem] border border-white/10 bg-white/6 p-4">
                            <div class="font-semibold">{{ auth()->user()->name }}</div>
                            <div class="mt-1 safe-wrap text-sm text-white/60">{{ auth()->user()->email }}</div>
                        </div>

                        <nav class="mt-6 space-y-1.5">
                            @foreach ($navigation as $item)
                                <a
                                    href="{{ route($item['route']) }}"
                                    @class([
                                        'sidebar-link',
                                        'sidebar-link--active' => request()->routeIs($item['pattern']),
                                    ])
                                    x-on:click="closeMobileNav()"
                                >
                                    <flux:icon :icon="$item['icon']" variant="mini" />
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </nav>

                        <div class="mt-6">
                            <div class="px-3 text-xs font-semibold uppercase tracking-[0.28em] text-white/40">Services</div>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($serviceLinks as $service)
                                    @if (is_object($service) && isset($service->id))
                                        <a
                                            href="{{ route('verifications.create', ['service' => $service->id]) }}"
                                            class="service-chip text-white/85"
                                            x-on:click="closeMobileNav()"
                                        >
                                            {{ strtoupper($service->code) }}
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <div class="mt-auto space-y-3">
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
                </div>

                @if (session('status'))
                    <flux:callout variant="success" icon="check-circle">
                        <flux:callout.heading>Status updated</flux:callout.heading>
                        <flux:callout.text>{{ session('status') }}</flux:callout.text>
                    </flux:callout>
                @endif

                @if (session('sms_status'))
                    <flux:callout variant="warning" icon="exclamation-triangle">
                        <flux:callout.heading>SMS update</flux:callout.heading>
                        <flux:callout.text>{{ session('sms_status') }}</flux:callout.text>
                    </flux:callout>
                @endif

                <main class="space-y-6">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </div>
</x-layouts.app>
