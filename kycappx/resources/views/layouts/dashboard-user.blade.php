<x-layouts.app :title="$title ?? 'Dashboard'">
    <div class="flex min-h-screen bg-gray-50">
        {{-- Sidebar --}}
        <aside class="hidden w-64 border-r bg-white lg:block">
            <div class="p-6 border-b">
                <div class="text-lg font-semibold">{{ config('app.name') }}</div>
                <div class="mt-1 text-xs text-gray-500">Customer Dashboard</div>
            </div>

            <nav class="p-4 space-y-1">
                <x-ui.nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                    Dashboard
                </x-ui.nav-link>

                <x-ui.nav-link href="{{ route('wallet') }}" :active="request()->routeIs('wallet')">
                    Wallet
                </x-ui.nav-link>

                <x-ui.nav-link href="{{ route('transactions') }}" :active="request()->routeIs('transactions')">
                    Transactions
                </x-ui.nav-link>

                <x-ui.nav-link href="{{ route('verifications.index') }}" :active="request()->routeIs('verifications.*')">
                    Verifications
                </x-ui.nav-link>

                <x-ui.nav-link href="{{ route('api.keys') }}" :active="request()->routeIs('api.keys')">
                    API Keys
                </x-ui.nav-link>

                @if(auth()->user()->hasAnyRole(['super-admin','admin','support']))
                    <div class="pt-4 mt-4 border-t">
                        <div class="px-3 text-xs font-semibold text-gray-400 uppercase">Admin</div>
                        <x-ui.nav-link href="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.*')">
                            Admin Panel
                        </x-ui.nav-link>
                    </div>
                @endif
            </nav>
        </aside>

        {{-- Main --}}
        <div class="flex-1">
            <header class="bg-white border-b">
                <div class="flex items-center justify-between px-4 py-3 lg:px-8">
                    <div class="font-semibold">{{ $header ?? 'Dashboard' }}</div>

                    <div class="flex items-center gap-3">
                        <span class="hidden text-sm text-gray-600 md:block">
                            {{ auth()->user()->email }}
                        </span>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="text-sm font-medium text-gray-700 hover:text-gray-900">Logout</button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="p-4 lg:p-8">
                @if (session('status'))
                    <div class="p-3 mb-4 text-sm border rounded bg-green-50 border-green-200 text-green-800">
                        {{ session('status') }}
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>
</x-layouts.app>