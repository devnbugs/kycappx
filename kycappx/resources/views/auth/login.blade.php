<x-guest-layout>
    <div class="space-y-8">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="section-kicker">Welcome Back</p>
                <h1 class="mt-3 text-3xl font-semibold text-slate-950 dark:text-slate-50">Sign in to your workspace.</h1>
                <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                    Manage wallet balances, dedicated virtual accounts, verification requests, and API access from a single responsive surface.
                </p>
            </div>

            <span class="badge-soft">Desktop, phone, tab</span>
        </div>

        <x-auth-session-status :status="session('status')" />

        @if ($siteSettings->google_auth_enabled)
            <a href="{{ route('social.redirect', 'google') }}" class="block">
                <flux:button variant="outline" class="w-full justify-center" icon="globe-alt">
                    Continue with Google
                </flux:button>
            </a>

            <div class="flex items-center gap-3 text-xs uppercase tracking-[0.28em] text-slate-400">
                <div class="h-px flex-1 bg-slate-200 dark:bg-white/10"></div>
                <span>or continue with password</span>
                <div class="h-px flex-1 bg-slate-200 dark:bg-white/10"></div>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <flux:field>
                <flux:label>Email or Username</flux:label>
                <flux:input name="login" type="text" :value="old('login')" required autofocus autocomplete="username" placeholder="you@example.com or username" />
                <flux:error name="login" />
            </flux:field>

            <flux:field>
                <flux:label>Password</flux:label>
                <flux:input name="password" type="password" required autocomplete="current-password" placeholder="Enter your password" />
                <flux:error name="password" />
            </flux:field>

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

            <div class="flex items-center justify-between gap-3">
                <p class="text-xs text-slate-500 dark:text-slate-400">2FA-enabled accounts will be prompted for an authenticator code after password sign-in.</p>

                <flux:button type="submit" variant="primary" color="teal">
                    {{ __('Log in') }}
                </flux:button>
            </div>
        </form>

        <p class="text-sm text-slate-600 dark:text-slate-300">
            New here?
            <a href="{{ route('register') }}" class="font-semibold text-slate-950 underline underline-offset-4 dark:text-white">Create an account</a>
        </p>
    </div>
</x-guest-layout>
