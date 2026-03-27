<x-layouts.dashboard-user title="Profile" header="Profile & Security">
    @php($googleAccount = $user->socialAccounts->firstWhere('provider', 'google'))

    @if (session('status') && ! in_array(session('status'), ['profile-updated', 'theme-updated', 'verification-link-sent'], true))
        <section class="surface-card p-5">
            <div class="rounded-[1.25rem] border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        </section>
    @endif

    <section class="grid gap-4 lg:grid-cols-[1.1fr,0.9fr]">
        <div class="account-card">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div class="text-sm uppercase tracking-[0.24em] text-white/60">Security posture</div>
                    <div class="mt-3 text-3xl font-semibold">{{ $user->hasTwoFactorEnabled() ? 'Two-factor active' : 'Strengthen your account' }}</div>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-white/75">
                        Manage your profile, preferred funding provider, password, Google sign-in linkage, and authenticator-based 2FA from this page.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <span class="badge-soft border-white/12 bg-white/10 text-white/85">{{ $user->isUserPro() ? 'User Pro' : 'Standard User' }}</span>
                    <span class="badge-soft border-white/12 bg-white/10 text-white/85">{{ strtoupper($user->preferred_funding_provider ?? ($siteSettings->default_funding_provider ?? 'paystack')) }} preferred</span>
                    <span class="badge-soft border-white/12 bg-white/10 text-white/85">{{ $kycSnapshot['level_label'] }}</span>
                </div>
            </div>
        </div>

        <flux:card>
            <flux:heading size="lg">Connected sign-ins</flux:heading>
            <flux:text class="mt-2">Google social authentication is {{ $siteSettings->google_auth_enabled ? 'enabled' : 'disabled' }} for this site.</flux:text>

            <div class="mt-5 space-y-3">
                @forelse ($user->socialAccounts as $socialAccount)
                    <div class="rounded-2xl border border-slate-200/80 bg-slate-50/80 p-4 dark:border-white/10 dark:bg-white/5">
                        <div class="font-semibold text-slate-950 dark:text-white">{{ ucfirst($socialAccount->provider) }}</div>
                        <div class="mt-1 text-sm text-slate-600 dark:text-slate-300">{{ $socialAccount->provider_email ?: 'Connected account' }}</div>
                        @if ($socialAccount->provider === 'google')
                            <div class="mt-3 flex flex-wrap gap-2">
                                <span class="badge-soft">{{ $user->googleLoginEnabled() ? 'Google login enabled' : 'Google login disabled' }}</span>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50/80 p-4 text-sm text-slate-500 dark:border-white/10 dark:bg-white/5 dark:text-slate-300">
                        No social sign-ins are linked yet.
                    </div>
                @endforelse
            </div>

            @if ($siteSettings->google_auth_enabled && ! $googleAccount)
                <div class="mt-5">
                    <x-auth.google-button
                        :href="route('social.redirect', ['provider' => 'google', 'intent' => 'link'])"
                        label="Link Google Sign-In"
                        :full-width="false"
                    />
                </div>
            @endif
        </flux:card>
    </section>

    <section class="surface-card p-6 sm:p-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="section-kicker">KYC Strength</p>
                <h2 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">Current profile: {{ $kycSnapshot['level_label'] }} at {{ $kycSnapshot['score'] }}%</h2>
                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $kycSnapshot['next_step'] }}</p>
            </div>

            <a href="{{ route('kyc.edit') }}">
                <x-ui.button>Open KYC Page</x-ui.button>
            </a>
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="surface-card p-6 sm:p-8 xl:col-span-2">
            <div class="max-w-3xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="surface-card p-6 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="surface-card p-6 sm:p-8">
            <div class="space-y-6">
                <div>
                    <h2 class="text-lg font-medium text-slate-900 dark:text-slate-50">Two-Factor Authentication</h2>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                        Add an authenticator app to protect wallet access, account cards, and service actions.
                    </p>
                </div>

                @if (! $user->two_factor_secret)
                    <form method="POST" action="{{ route('profile.two-factor.store') }}">
                        @csrf
                        <flux:button type="submit" variant="primary" color="teal">Enable 2FA</flux:button>
                    </form>
                @elseif (! $user->hasTwoFactorEnabled())
                    <div class="rounded-[1.5rem] border border-slate-200/80 bg-slate-50/80 p-4 dark:border-white/10 dark:bg-white/5">
                        <div class="font-semibold text-slate-950 dark:text-white">Scan the QR code in your authenticator app</div>
                        <div class="mt-4 flex justify-center rounded-[1.5rem] bg-white p-4">
                            {!! $twoFactorQrCode !!}
                        </div>
                    </div>

                    <form method="POST" action="{{ route('profile.two-factor.update') }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <flux:field>
                            <flux:label>Verification Code</flux:label>
                            <flux:input name="code" type="text" required placeholder="123456" />
                            <flux:error name="two_factor" />
                        </flux:field>

                        <div class="flex flex-wrap gap-3">
                            <flux:button type="submit" variant="primary" color="teal">Confirm 2FA</flux:button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('profile.two-factor.destroy') }}">
                        @csrf
                        @method('DELETE')
                        <flux:button type="submit" variant="ghost">Cancel setup</flux:button>
                    </form>
                @else
                    <div class="rounded-[1.5rem] border border-slate-200/80 bg-slate-50/80 p-4 dark:border-white/10 dark:bg-white/5">
                        <div class="font-semibold text-slate-950 dark:text-white">Recovery codes</div>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Use these only if your authenticator app is unavailable.</p>
                        <div class="mt-4 grid gap-2 sm:grid-cols-2">
                            @foreach ($recoveryCodes as $recoveryCode)
                                <div class="rounded-xl bg-white px-3 py-2 font-mono text-sm text-slate-800 shadow-sm dark:bg-slate-950 dark:text-slate-100">{{ $recoveryCode }}</div>
                            @endforeach
                        </div>
                    </div>

                    <form method="POST" action="{{ route('profile.two-factor.destroy') }}">
                        @csrf
                        @method('DELETE')
                        <flux:button type="submit" variant="ghost">Disable 2FA</flux:button>
                    </form>
                @endif
            </div>
        </div>

        <div class="surface-card p-6 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-layouts.dashboard-user>
