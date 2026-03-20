<x-guest-layout>
    <div class="space-y-8">
        <div>
            <p class="section-kicker">Get Started</p>
            <h1 class="mt-3 text-3xl font-semibold text-slate-950 dark:text-slate-50">Create your Kycappx workspace.</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                Join the Space
            </p>
        </div>

        @if ($siteSettings->google_auth_enabled)
            <a href="{{ route('social.redirect', 'google') }}" class="block">
                <flux:button variant="outline" class="w-full justify-center" icon="globe-alt">
                    Sign up with Google
                </flux:button>
            </a>

            <div class="flex items-center gap-3 text-xs uppercase tracking-[0.28em] text-slate-400">
                <div class="h-px flex-1 bg-slate-200 dark:bg-white/10"></div>
                <span>or create with email</span>
                <div class="h-px flex-1 bg-slate-200 dark:bg-white/10"></div>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf

            <div class="grid gap-5 md:grid-cols-2">
                <flux:field>
                    <flux:label>Name</flux:label>
                    <flux:input name="name" type="text" :value="old('name')" required autofocus autocomplete="name" placeholder="Rabiu Salisu" />
                    <flux:error name="name" />
                </flux:field>

                <flux:field>
                    <flux:label>Username</flux:label>
                    <flux:input name="username" type="text" :value="old('username')" required autocomplete="username" placeholder="rhsalisu" />
                    <flux:error name="username" />
                </flux:field>

                <flux:field class="md:col-span-2">
                    <flux:label>Email</flux:label>
                    <flux:input name="email" type="email" :value="old('email')" required autocomplete="email" placeholder="you@example.com" />
                    <flux:error name="email" />
                </flux:field>

                <flux:field>
                    <flux:label>Password</flux:label>
                    <flux:input name="password" type="password" required autocomplete="new-password" placeholder="Create a secure password" />
                    <flux:error name="password" />
                </flux:field>

                <flux:field>
                    <flux:label>Confirm Password</flux:label>
                    <flux:input name="password_confirmation" type="password" required autocomplete="new-password" placeholder="Repeat password" />
                    <flux:error name="password_confirmation" />
                </flux:field>
            </div>

            <x-security.turnstile action="register" />

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <a class="text-sm font-medium text-slate-600 underline-offset-4 transition hover:text-slate-950 hover:underline dark:text-slate-300 dark:hover:text-slate-50" href="{{ route('login') }}">
                    {{ __('Already registered?') }}
                </a>

                <flux:button type="submit" variant="primary" color="teal">
                    {{ __('Register') }}
                </flux:button>
            </div>
        </form>

        <div class="rounded-[1.5rem] border border-slate-200/80 bg-slate-50/90 p-4 text-sm text-slate-600 dark:border-white/10 dark:bg-white/5 dark:text-slate-300">
            <div class="font-semibold text-slate-950 dark:text-white">Upgrade path</div>
            <p class="mt-2">Admins can promote accounts to <span class="font-semibold">User Pro</span> for discount-aware service pricing and stronger security defaults like 2FA.</p>
        </div>
    </div>
</x-guest-layout>
