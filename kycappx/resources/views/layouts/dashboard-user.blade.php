@php
    $navigation = [
        ['label' => 'Overview', 'route' => 'dashboard', 'pattern' => 'dashboard'],
        ['label' => 'Wallet', 'route' => 'wallet', 'pattern' => 'wallet*'],
        ['label' => 'Transactions', 'route' => 'transactions', 'pattern' => 'transactions'],
        ['label' => 'Verifications', 'route' => 'verifications.index', 'pattern' => 'verifications.*'],
        ['label' => 'API Keys', 'route' => 'api.keys', 'pattern' => 'api.keys*'],
        ['label' => 'Profile', 'route' => 'profile.edit', 'pattern' => 'profile.*'],
    ];
    $roleNames = auth()->user()->getRoleNames();
    $roleLabel = $roleNames->isNotEmpty()
        ? $roleNames->map(fn ($role) => \Illuminate\Support\Str::headline($role))->implode(' / ')
        : 'Customer';
@endphp

<x-layouts.app :title="$title ?? 'Customer Workspace'">
    <div class="min-h-screen px-4 py-4 sm:px-6 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-4 lg:grid-cols-[280px,minmax(0,1fr)]">
            <aside class="surface-card hidden overflow-hidden bg-slate-950 text-slate-100 dark:border-slate-800 dark:bg-slate-950 lg:block">
                <div class="flex h-full flex-col p-5">
                    <div>
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 text-lg font-bold shadow-lg">KX</div>
                            <div>
                                <div class="text-lg font-semibold">{{ $siteSettings->site_name ?? config('app.name') }}</div>
                                <div class="text-sm text-slate-300">Customer workspace</div>
                            </div>
                        </div>

                        <div class="mt-6 rounded-[1.5rem] border border-white/10 bg-white/5 p-4">
                            <div class="text-sm font-semibold text-white">{{ auth()->user()->name }}</div>
                            <div class="mt-1 text-sm text-slate-300">{{ auth()->user()->email }}</div>
                            <div class="mt-4 badge-soft border-white/10 bg-white/10 text-slate-100">
                                {{ $roleLabel }}
                            </div>
                        </div>
                    </div>

                    <nav class="mt-6 space-y-2">
                        @foreach ($navigation as $item)
                            <x-ui.nav-link href="{{ route($item['route']) }}" :active="request()->routeIs($item['pattern'])">
                                {{ $item['label'] }}
                            </x-ui.nav-link>
                        @endforeach

                        @if (auth()->user()->isAdmin())
                            <div class="pt-4">
                                <div class="px-4 text-xs font-semibold uppercase tracking-[0.24em] text-slate-400">Operations</div>
                                <div class="mt-2">
                                    <x-ui.nav-link href="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.*')">
                                        Admin Panel
                                    </x-ui.nav-link>
                                </div>
                            </div>
                        @endif
                    </nav>

                    <div class="mt-auto pt-6">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-ui.button type="submit" variant="secondary" class="w-full border-white/10 bg-white/5 text-white hover:bg-white/10 hover:text-white">
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
                            <p class="section-kicker">Customer Workspace</p>
                            <h1 class="mt-3 text-3xl font-semibold text-slate-950 text-balance dark:text-slate-50">{{ $header ?? 'Operations overview' }}</h1>
                            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                                Keep wallet operations, verification runs, and credential management moving from one clear surface.
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <a href="{{ route('profile.edit') }}">
                                <x-ui.button variant="secondary">Profile</x-ui.button>
                            </a>

                            @if (auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}">
                                    <x-ui.button>Admin Panel</x-ui.button>
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="mt-5 flex gap-2 overflow-x-auto pb-1 lg:hidden">
                        @foreach ($navigation as $item)
                            <a
                                href="{{ route($item['route']) }}"
                                @class([
                                    'whitespace-nowrap rounded-full px-4 py-2 text-sm font-semibold transition',
                                    'bg-slate-950 text-white' => request()->routeIs($item['pattern']),
                                    'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700' => ! request()->routeIs($item['pattern']),
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
