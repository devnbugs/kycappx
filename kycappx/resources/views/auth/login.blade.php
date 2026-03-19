<x-guest-layout>
    <div class="space-y-8">
        <div>
            <p class="section-kicker">Welcome Back</p>
            <h1 class="mt-3 text-3xl font-semibold text-slate-950 dark:text-slate-50">Sign in to your workspace.</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                Manage wallet balances, verification requests, and API access from a single control surface.
            </p>
        </div>

        <x-auth-session-status :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <x-input-label for="login" :value="__('Email or Username')" />
                <x-text-input id="login" class="mt-2 block w-full" type="text" name="login" :value="old('login')" required autofocus autocomplete="username" />
                <x-input-error :messages="$errors->get('login')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="mt-2 block w-full" type="password" name="password" required autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <label for="remember_me" class="inline-flex items-center gap-3 text-sm text-slate-600 dark:text-slate-300">
                    <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-slate-900 shadow-sm focus:ring-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-teal-400 dark:focus:ring-teal-500" name="remember">
                    <span>{{ __('Remember me') }}</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="text-sm font-medium text-slate-600 underline-offset-4 transition hover:text-slate-950 hover:underline dark:text-slate-300 dark:hover:text-slate-50" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif
            </div>

            <div class="flex items-center justify-end">
                <x-primary-button>
                    {{ __('Log in') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>
