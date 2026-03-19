<x-layouts.app :title="$title ?? 'Admin'">
    <div class="flex min-h-screen bg-gray-50">
        <aside class="hidden w-72 border-r bg-white lg:block">
            <div class="p-6 border-b">
                <div class="text-lg font-semibold">{{ config('app.name') }}</div>
                <div class="mt-1 text-xs text-gray-500">Admin Dashboard</div>
            </div>

            <nav class="p-4 space-y-1">
                <x-ui.nav-link href="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.dashboard')">
                    Overview
                </x-ui.nav-link>

                {{-- add more later --}}
                <div class="pt-4 mt-4 border-t">
                    <x-ui.nav-link href="{{ route('dashboard') }}" :active="false">
                        Back to Customer
                    </x-ui.nav-link>
                </div>
            </nav>
        </aside>

        <div class="flex-1">
            <header class="bg-white border-b">
                <div class="flex items-center justify-between px-4 py-3 lg:px-8">
                    <div class="font-semibold">{{ $header ?? 'Admin' }}</div>

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