@php
    $accountsByProvider = $virtualAccounts->keyBy('provider');
@endphp

<x-layouts.dashboard-user title="Dashboard" header="Command Center">
    <section class="grid gap-4 xl:grid-cols-[1.25fr,0.75fr]">
        <div class="hero-tile surface-card relative overflow-hidden p-8 text-white">
            <div class="absolute -right-10 top-0 h-48 w-48 rounded-full bg-amber-300/20 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 h-52 w-52 rounded-full bg-teal-400/20 blur-3xl"></div>

            <div class="relative">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="badge-soft border-white/10 bg-white/10 text-white/85">{{ auth()->user()->isUserPro() ? 'User Pro' : 'Standard User' }}</span>
                    @if ($discountRate > 0)
                        <span class="badge-soft border-white/10 bg-white/10 text-white/85">{{ rtrim(rtrim(number_format($discountRate, 2), '0'), '.') }}% service discount</span>
                    @endif
                </div>

                <h2 class="mt-5 max-w-3xl text-3xl font-semibold text-balance">Welcome Back!</h2>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-200/80">
                    Your Balance Will Be Updated Every Page Refresh.
                </p>

                <div class="mt-8 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-[1.5rem] border border-white/10 bg-white/10 p-5">
                        <div class="text-sm text-white/60">Available balance</div>
                        <div class="mt-2 text-4xl font-semibold">NGN {{ number_format($stats['wallet_balance'], 2) }}</div>
                    </div>
                    <!--div class="rounded-[1.5rem] border border-white/10 bg-white/10 p-5">
                        <div class="text-sm text-white/60">Account cards</div>
                        <div class="mt-2 text-4xl font-semibold">{{ $virtualAccounts->count() }}</div>
                        <div class="mt-2 text-sm text-white/65">Paystack and Kora DVA provisioning</div>
                    </div-->
                </div>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('kyc.edit') }}">
                        <flux:button variant="outline" class="border-white/20 bg-white/10 text-white hover:bg-white/15">Account Status</flux:button>
                    </a>
                    <a href="{{ route('verifications.create') }}">
                        <flux:button variant="primary" color="teal" icon="shield-check"></flux:button>
                    </a>
                    <a href="{{ route('wallet') }}">
                        <flux:button variant="outline" class="border-white/20 bg-white/10 text-white hover:bg-white/15">Manage wallet</flux:button>
                    </a>
                </div>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
            <div class="metric-card">
                <div class="text-sm text-slate-500 dark:text-slate-400">Wallet balance</div>
                <div class="mt-3 text-3xl font-semibold text-slate-950 dark:text-slate-50">NGN {{ number_format($stats['wallet_balance'], 2) }}</div>
                <div class="mt-2 text-sm text-slate-600 dark:text-slate-300">Current wallet status: {{ ucfirst($wallet->status) }}</div>
            </div>

            <div class="metric-card">
                <div class="text-sm text-slate-500 dark:text-slate-400">Transactions</div>
                <div class="mt-3 text-3xl font-semibold text-slate-950 dark:text-slate-50">{{ number_format($stats['transaction_count']) }}</div>
                <div class="mt-2 text-sm text-slate-600 dark:text-slate-300">Ledger entries connected to your wallet.</div>
            </div>

            <div class="metric-card">
                <div class="text-sm text-slate-500 dark:text-slate-400">Verifications</div>
                <div class="mt-3 text-3xl font-semibold text-slate-950 dark:text-slate-50">{{ number_format($stats['verification_count']) }}</div>
                <div class="mt-2 text-sm text-slate-600 dark:text-slate-300">Submitted verification requests across all services.</div>
            </div>

            <!--div class="metric-card">
                <div class="text-sm text-slate-500 dark:text-slate-400">Active API keys</div>
                <div class="mt-3 text-3xl font-semibold text-slate-950 dark:text-slate-50">{{ number_format($stats['active_api_keys']) }}</div>
                <div class="mt-2 text-sm text-slate-600 dark:text-slate-300">Keys that can still access your account.</div>
            </div>

            <div class="metric-card">
                <div class="text-sm text-slate-500 dark:text-slate-400">KYC strength</div>
                <div class="mt-3 text-3xl font-semibold text-slate-950 dark:text-slate-50">{{ $kycSnapshot['level_label'] }}</div>
                <div class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ $kycSnapshot['score'] }}% profile strength toward {{ $kycSnapshot['target_level'] }}.</div>
            </div>

            <div class="metric-card">
                <div class="text-sm text-slate-500 dark:text-slate-400">Preferred funding</div>
                <div class="mt-3 text-3xl font-semibold text-slate-950 dark:text-slate-50">{{ strtoupper(auth()->user()->preferred_funding_provider ?? ($siteSettings->default_funding_provider ?? 'paystack')) }}</div>
                <div class="mt-2 text-sm text-slate-600 dark:text-slate-300">Your default provider for DVA and funding flows.</div>
            </div-->
        </div>
    </section>

    <!--section class="grid gap-4 lg:grid-cols-2">
        @foreach ($virtualAccountProviders as $provider)
            @php($account = $accountsByProvider->get($provider['code']))
            <div class="{{ $account?->account_number ? 'account-card' : 'surface-card p-6 sm:p-8' }}">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm {{ $account?->account_number ? 'text-white/60' : 'text-slate-500 dark:text-slate-400' }}">{{ $provider['name'] }}</div>
                        <div class="mt-2 text-2xl font-semibold {{ $account?->account_number ? 'text-white' : 'text-slate-950 dark:text-white' }}">
                            {{ $account?->account_number ?: 'No account card yet' }}
                        </div>
                        <div class="mt-2 text-sm {{ $account?->account_number ? 'text-white/70' : 'text-slate-600 dark:text-slate-300' }}">
                            {{ $account?->bank_name ?: $provider['description'] }}
                        </div>
                    </div>
                    <x-ui.status-badge :value="$account?->status ?? ($provider['enabled'] ? 'Ready' : 'Disabled')" :tone="match ($account?->status ?? null) {
                        'active' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        default => $provider['enabled'] ? 'info' : 'warning',
                    }" />
                </div>

                @if ($account?->account_name)
                    <div class="mt-6 grid gap-2 text-sm {{ $account?->account_number ? 'text-white/80' : 'text-slate-600 dark:text-slate-300' }}">
                        <div>Account Name: <span class="font-semibold">{{ $account->account_name }}</span></div>
                        <div>Provider Ref: <span class="font-mono text-xs">{{ $account->provider_reference ?: 'Pending' }}</span></div>
                    </div>
                @endif

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('wallet') }}">
                        <flux:button variant="{{ $account?->account_number ? 'outline' : 'primary' }}" color="teal">
                            {{ $account?->account_number ? 'Open wallet' : 'Create account card' }}
                        </flux:button>
                    </a>
                    @if ($account?->account_number)
                        <span class="service-chip border-white/10 bg-white/10 text-white/85">Top up by transfer</span>
                    @endif
                </div>
            </div>
        @endforeach
    </section-->

    <section class="grid gap-4 lg:grid-cols-[0.9fr,1.1fr]">
        <x-dashboard.verification-launchpad
            :services="$activeServices"
            title="Launch Verification Fast"
            copy="Run the most-used Prembly checks without hunting through the catalog first."
            button-label="Browse All Services"
        />

        <div class="table-shell">
            <div class="flex items-center justify-between gap-3 px-6 py-5">
                    <div>
                        <p class="section-kicker">Recent Verification Activity</p>
                        <h3 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">Latest requests</h3>
                    </div>
                <a href="{{ route('verifications.index') }}">
                    <x-ui.button variant="secondary">View All</x-ui.button>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="table-header">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Service</th>
                            <th class="px-6 py-4 text-left font-semibold">Reference</th>
                            <th class="px-6 py-4 text-left font-semibold">Status</th>
                            <th class="px-6 py-4 text-left font-semibold">Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentVerifications as $verification)
                            <tr class="table-row">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-950 dark:text-slate-50">{{ $verification->service?->name ?? 'Unknown service' }}</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">{{ $verification->provider_used ?: 'Awaiting provider' }}</div>
                                </td>
                                <td class="px-6 py-4 font-mono text-xs text-slate-700 dark:text-slate-300">{{ $verification->reference }}</td>
                                <td class="px-6 py-4">
                                    <x-ui.status-badge
                                        :value="$verification->status"
                                        :tone="match ($verification->status) {
                                            'success' => 'success',
                                            'failed' => 'danger',
                                            'manual_review' => 'warning',
                                            default => 'info',
                                        }"
                                    />
                                </td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $verification->created_at?->format('M d, Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr class="table-row">
                                <td colspan="4" class="px-6 py-8 text-center text-slate-500 dark:text-slate-400">No verification requests have been submitted yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="table-shell">
        <div class="flex items-center justify-between gap-3 px-6 py-5">
            <div>
                <p class="section-kicker">Recent Wallet Activity</p>
                <h3 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">Latest transactions</h3>
            </div>
            <a href="{{ route('transactions') }}">
                <x-ui.button variant="secondary">Open Ledger</x-ui.button>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="table-header">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold">Reference</th>
                        <th class="px-6 py-4 text-left font-semibold">Type</th>
                        <th class="px-6 py-4 text-left font-semibold">Amount</th>
                        <th class="px-6 py-4 text-left font-semibold">Source</th>
                        <th class="px-6 py-4 text-left font-semibold">Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentTransactions as $transaction)
                        <tr class="table-row">
                            <td class="px-6 py-4 font-mono text-xs text-slate-700 dark:text-slate-300">{{ $transaction->reference }}</td>
                            <td class="px-6 py-4">
                                <x-ui.status-badge
                                    :value="$transaction->type"
                                    :tone="match ($transaction->type) {
                                        'credit' => 'success',
                                        'debit' => 'warning',
                                        default => 'slate',
                                    }"
                                />
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-950 dark:text-slate-50">NGN {{ number_format((float) $transaction->amount, 2) }}</td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $transaction->source ?: 'Internal' }}</td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $transaction->created_at?->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr class="table-row">
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500 dark:text-slate-400">No wallet activity recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.dashboard-user>
