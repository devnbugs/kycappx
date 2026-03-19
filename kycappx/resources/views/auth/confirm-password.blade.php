<x-guest-layout>
    <div class="space-y-8">
        <div>
            <p class="section-kicker">Security Check</p>
            <h1 class="mt-3 text-3xl font-semibold text-slate-950">Confirm your password.</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">
                This area controls sensitive account activity. Re-enter your password to continue.
            </p>
        </div>

        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
            @csrf

            <div>
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="mt-2 block w-full" type="password" name="password" required autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <x-security.turnstile action="password_confirm" />

            <div class="flex justify-end">
                <x-primary-button>
                    {{ __('Confirm') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>
