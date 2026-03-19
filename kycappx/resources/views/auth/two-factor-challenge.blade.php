<x-guest-layout>
    <div class="space-y-8">
        <div>
            <p class="section-kicker">Security Check</p>
            <h1 class="mt-3 text-3xl font-semibold text-slate-950 dark:text-slate-50">Complete two-factor authentication.</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">
                Enter the 6-digit code from your authenticator app, or use one of your saved recovery codes.
            </p>
        </div>

        <form method="POST" action="{{ route('two-factor.challenge.store') }}" class="space-y-5">
            @csrf

            <flux:field>
                <flux:label>Authenticator Code or Recovery Code</flux:label>
                <flux:input name="code" type="text" required autofocus placeholder="123456 or ABCDE-12345" />
                <flux:error name="code" />
            </flux:field>

            <x-security.turnstile action="two_factor" />

            <div class="flex flex-wrap items-center gap-3">
                <flux:button type="submit" variant="primary" color="teal">Verify and continue</flux:button>
            </div>
        </form>

        <form method="POST" action="{{ route('two-factor.challenge.cancel') }}">
            @csrf
            <flux:button type="submit" variant="ghost">Back to sign in</flux:button>
        </form>
    </div>
</x-guest-layout>
