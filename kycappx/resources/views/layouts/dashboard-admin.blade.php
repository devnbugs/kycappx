@php
    $navigation = [
        ['label' => 'Overview', 'route' => 'admin.dashboard', 'pattern' => 'admin.dashboard'],
        ['label' => 'Users', 'route' => 'admin.users.index', 'pattern' => ['admin.users.*', 'admin.customers.*']],
        ['label' => 'Services', 'route' => 'admin.services.index', 'pattern' => 'admin.services.*'],
        ['label' => 'Providers', 'route' => 'admin.providers.index', 'pattern' => 'admin.providers.*'],
        ['label' => 'Site Settings', 'route' => 'admin.settings.site', 'pattern' => 'admin.settings.*'],
        ['label' => 'Webhook Logs', 'route' => 'admin.logs.webhooks', 'pattern' => 'admin.logs.webhooks'],
        ['label' => 'Verification Logs', 'route' => 'admin.logs.verifications', 'pattern' => 'admin.logs.verifications'],
        ['label' => 'Audit Logs', 'route' => 'admin.logs.audit', 'pattern' => 'admin.logs.audit'],
    ];
@endphp

<x-layouts.app :title="$title ?? 'Admin Workspace'">
    <div class="min-h-screen px-4 py-4 sm:px-6 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-4 lg:grid-cols-[300px,minmax(0,1fr)]">
            <aside class="surface-card hidden overflow-hidden bg-slate-950 text-slate-100 dark:border-slate-800 dark:bg-slate-950 lg:block">
                <div class="flex h-full flex-col p-5">
                    <div>
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 text-lg font-bold shadow-lg">OP</div>
                            <div>
                                <div class="text-lg font-semibold">{{ $siteSettings->site_name ?? config('app.name') }}</div>
                                <div class="text-sm text-slate-300">Operations cockpit</div>
                            </div>
                        </div>

                        <div class="mt-6 rounded-[1.5rem] border border-white/10 bg-white/5 p-4">
                            <div class="text-xs uppercase tracking-[0.24em] text-slate-400">Signed in as</div>
                            <div class="mt-3 text-lg font-semibold text-white">{{ auth()->user()->name }}</div>
                            <div class="mt-1 text-sm text-slate-300">{{ auth()->user()->email }}</div>
                        </div>
                    </div>

                    <nav class="mt-6 space-y-2">
                        @foreach ($navigation as $item)
                            <x-ui.nav-link href="{{ route($item['route']) }}" :active="request()->routeIs(...(array) $item['pattern'])">
                                {{ $item['label'] }}
                            </x-ui.nav-link>
                        @endforeach
                    </nav>

                    <div class="mt-auto space-y-3 pt-6">
                        <a href="{{ route('dashboard') }}" class="block">
                            <x-ui.button variant="secondary" class="w-full border-white/10 bg-white/5 text-white hover:bg-white/10 hover:text-white">
                                Back to Customer View
                            </x-ui.button>
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-ui.button type="submit" variant="secondary" class="w-full border-white/10 bg-transparent text-white hover:bg-white/10 hover:text-white">
                                Sign Out
                            </x-ui.button>
                        </form>
                    </div>
                </div>
            </aside>

            <div class="space-y-4">
                <header class="surface-card p-4 sm:p-6">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                        <div>
                            <p class="section-kicker">Admin Workspace</p>
                            <h1 class="mt-3 text-3xl font-semibold text-slate-950 text-balance dark:text-slate-50">{{ $header ?? 'Operations overview' }}</h1>
                            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                                Monitor customer activity, service readiness, provider health, and delivery logs from a single operations cockpit.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('admin.settings.site') }}">
                                <x-ui.button variant="secondary">Site Settings</x-ui.button>
                            </a>
                            <a href="{{ route('dashboard') }}">
                                <x-ui.button variant="secondary">Customer Workspace</x-ui.button>
                            </a>
                        </div>
                    </div>

                    <div class="mt-5 flex gap-2 overflow-x-auto pb-1 lg:hidden">
                        @foreach ($navigation as $item)
                            <a
                                href="{{ route($item['route']) }}"
                                @class([
                                    'whitespace-nowrap rounded-full px-4 py-2 text-sm font-semibold transition',
                                    'bg-slate-950 text-white' => request()->routeIs(...(array) $item['pattern']),
                                    'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700' => ! request()->routeIs(...(array) $item['pattern']),
                                ])
                            >
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                    </div>
                </header>

                @if (session('status'))
                    <div class="surface-card border-emerald-200/80 bg-emerald-50/90 px-5 py-4 text-sm font-medium text-emerald-900 dark:border-emerald-900/60 dark:bg-emerald-950/50 dark:text-emerald-200">
                        {{ session('status') }}
                    </div>
                @endif

                <main class="space-y-6">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </div>
</x-layouts.app>
