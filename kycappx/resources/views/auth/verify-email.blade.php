<x-guest-layout>
    <div class="space-y-8">
        <div>
            <p class="section-kicker">One Last Step</p>
            <h1 class="mt-3 text-3xl font-semibold text-slate-950">Verify your email.</h1>
            <p class="mt-2 text-sm leading-6 text-slate-600">
                Confirm your email address to activate your workspace and keep sensitive verification flows secure.
            </p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </div>
        @endif

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <x-primary-button>
                    {{ __('Resend Verification Email') }}
                </x-primary-button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm font-medium text-slate-600 underline-offset-4 transition hover:text-slate-950 hover:underline">
                    {{ __('Log Out') }}
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
