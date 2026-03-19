<x-guest-layout>
    <div class="space-y-8">
        <div>
            <p class="section-kicker">Recovery</p>
            <h1 class="mt-3 text-3xl font-semibold text-slate-950">Reset your password.</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">
                Enter the email address for your workspace and we will send you a reset link.
            </p>
        </div>

        <x-auth-session-status :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf

            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" class="mt-2 block w-full" type="email" name="email" :value="old('email')" required autofocus />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <x-security.turnstile action="password_email" />

            <div class="flex items-center justify-end">
                <x-primary-button>
                    {{ __('Email Password Reset Link') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>
