@php
    $accountsByProvider = $virtualAccounts->keyBy('provider');
    $paystackAccount = $accountsByProvider->get('paystack');
    $koraAccount = $accountsByProvider->get('kora');
@endphp

<x-layouts.dashboard-user title="Wallet" header="Wallet Operations">
    @if ($errors->has('virtual_accounts'))
        <div class="rounded-[1.5rem] border border-rose-200 bg-rose-50/90 px-5 py-4 text-sm font-medium text-rose-900 dark:border-rose-900/40 dark:bg-rose-950/30 dark:text-rose-200">
            {{ $errors->first('virtual_accounts') }}
        </div>
    @endif

    <section class="grid gap-4 xl:grid-cols-[0.9fr,1.1fr]">
        <div class="account-card">
            <div class="text-sm text-white/60">Available Balance</div>
            <div class="mt-4 text-5xl font-semibold">NGN {{ number_format((float) $wallet->balance, 2) }}</div>
            <div class="mt-3 text-sm text-white/75">
                Wallet status: {{ ucfirst($wallet->status) }} · Currency: {{ strtoupper($wallet->currency) }}
            </div>

            <div class="mt-8 flex flex-wrap gap-2 text-xs">
                <span class="badge-soft border-white/10 bg-white/10 text-white">Kora {{ $gatewayStatus['kora'] ? 'configured' : 'not configured' }}</span>
                <span class="badge-soft border-white/10 bg-white/10 text-white">Paystack {{ $gatewayStatus['paystack'] ? 'configured' : 'not configured' }}</span>
            </div>
        </div>

        <div class="surface-card p-6 sm:p-8">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="section-kicker">Fund Wallet</p>
                    <h2 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">Initialize a new Kora top-up</h2>
                </div>
                <x-ui.status-badge :value="$siteSettings->wallet_funding_enabled ? ($gatewayStatus['kora'] ? 'Live' : 'Setup Required') : 'Disabled'" :tone="$siteSettings->wallet_funding_enabled ? ($gatewayStatus['kora'] ? 'success' : 'warning') : 'warning'" />
            </div>

            <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                Wallet credits are only recorded after a signed webhook confirms the payment, so the balance stays idempotent even if Kora retries the callback.
            </p>

            <form method="POST" action="{{ route('wallet.fund.kora') }}" class="mt-6 space-y-5">
                @csrf

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="amount" value="Amount (NGN)" />
                        <x-text-input id="amount" name="amount" type="number" min="100" step="50" class="mt-2" :value="old('amount', 2500)" />
                        <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="currency" value="Currency" />
                        <x-text-input id="currency" name="currency" type="text" maxlength="3" class="mt-2 uppercase" :value="old('currency', 'NGN')" />
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    @if ($gatewayStatus['kora'] && $siteSettings->wallet_funding_enabled)
                        <x-ui.button type="submit">
                            Continue to Kora Checkout
                        </x-ui.button>
                    @else
                        <x-ui.button type="submit" variant="secondary" disabled>
                            Continue to Kora Checkout
                        </x-ui.button>
                    @endif

                    <a href="{{ route('transactions') }}">
                        <x-ui.button variant="secondary">Review Transactions</x-ui.button>
                    </a>
                </div>
            </form>

            @unless ($gatewayStatus['kora'] && $siteSettings->wallet_funding_enabled)
                <div class="mt-5 rounded-[1.5rem] border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-900 dark:border-amber-900/60 dark:bg-amber-950/50 dark:text-amber-200">
                    {{ $siteSettings->wallet_funding_enabled ? 'Add `KORA_SECRET_KEY` and `KORA_REDIRECT_URL` to enable production wallet funding.' : 'Wallet funding has been disabled by the site administrator.' }}
                </div>
            @endunless
        </div>
    </section>

    <section class="grid gap-4 lg:grid-cols-2">
        <div class="surface-card p-6 sm:p-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="section-kicker">Paystack DVA</p>
                    <h3 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">
                        {{ $paystackAccount?->account_number ?: 'Create an account card' }}
                    </h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                        Assign a Paystack dedicated account so customers can top up by direct bank transfer and receive wallet credits through webhooks.
                    </p>
                </div>
                <x-ui.status-badge :value="$paystackAccount?->status ?? ($gatewayStatus['paystack'] ? 'Ready' : 'Setup Required')" :tone="match ($paystackAccount?->status ?? null) {
                    'active' => 'success',
                    'pending' => 'warning',
                    'failed' => 'danger',
                    default => $gatewayStatus['paystack'] ? 'info' : 'warning',
                }" />
            </div>

            @if ($paystackAccount?->account_number)
                <div class="mt-6 rounded-[1.5rem] border border-slate-200/80 bg-slate-50/80 p-5 dark:border-white/10 dark:bg-white/5">
                    <div class="text-sm text-slate-500 dark:text-slate-400">{{ $paystackAccount->bank_name }}</div>
                    <div class="mt-2 font-mono text-3xl font-semibold text-slate-950 dark:text-white">{{ $paystackAccount->account_number }}</div>
                    <div class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ $paystackAccount->account_name }}</div>
                </div>

                <div class="mt-5 flex flex-wrap gap-3">
                    <form method="POST" action="{{ route('wallet.accounts.requery', $paystackAccount) }}">
                        @csrf
                        <flux:button type="submit" variant="outline">Requery transfers</flux:button>
                    </form>
                </div>
            @else
                <form method="POST" action="{{ route('wallet.accounts.store', ['provider' => 'paystack']) }}" class="mt-6">
                    @csrf
                    <flux:button type="submit" variant="primary" color="teal" :disabled="! ($siteSettings->dva_enabled && $siteSettings->paystack_dva_enabled && $gatewayStatus['paystack'])">
                        Assign Paystack account
                    </flux:button>
                </form>
            @endif
        </div>

        <div class="surface-card p-6 sm:p-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="section-kicker">Kora Virtual Account</p>
                    <h3 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">
                        {{ $koraAccount?->account_number ?: 'Create a Kora virtual account' }}
                    </h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                        Kora permanent virtual accounts require BVN-backed KYC. Once assigned, incoming transfers can credit the wallet automatically.
                    </p>
                </div>
                <x-ui.status-badge :value="$koraAccount?->status ?? ($gatewayStatus['kora'] ? 'Ready' : 'Setup Required')" :tone="match ($koraAccount?->status ?? null) {
                    'active' => 'success',
                    'pending' => 'warning',
                    'failed' => 'danger',
                    default => $gatewayStatus['kora'] ? 'info' : 'warning',
                }" />
            </div>

            @if ($koraAccount?->account_number)
                <div class="mt-6 rounded-[1.5rem] border border-slate-200/80 bg-slate-50/80 p-5 dark:border-white/10 dark:bg-white/5">
                    <div class="text-sm text-slate-500 dark:text-slate-400">{{ $koraAccount->bank_name }}</div>
                    <div class="mt-2 font-mono text-3xl font-semibold text-slate-950 dark:text-white">{{ $koraAccount->account_number }}</div>
                    <div class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ $koraAccount->account_name }}</div>
                </div>
            @else
                <form method="POST" action="{{ route('wallet.accounts.store', ['provider' => 'kora']) }}" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="kora_bvn" value="BVN" />
                        <x-text-input id="kora_bvn" name="bvn" type="text" class="mt-2" :value="old('bvn')" placeholder="22123456789" />
                    </div>

                    <div>
                        <x-input-label for="kora_nin" value="NIN (Optional)" />
                        <x-text-input id="kora_nin" name="nin" type="text" class="mt-2" :value="old('nin')" placeholder="12345678901" />
                    </div>

                    <flux:button type="submit" variant="primary" color="teal" :disabled="! ($siteSettings->dva_enabled && $siteSettings->kora_dva_enabled && $gatewayStatus['kora'])">
                        Assign Kora account
                    </flux:button>
                </form>
            @endif
        </div>
    </section>

    <section class="grid gap-4 lg:grid-cols-2">
        <div class="table-shell">
                <div class="px-6 py-5">
                    <p class="section-kicker">Funding Requests</p>
                    <h3 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">Latest top-up attempts</h3>
                </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="table-header">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Reference</th>
                            <th class="px-6 py-4 text-left font-semibold">Amount</th>
                            <th class="px-6 py-4 text-left font-semibold">Status</th>
                            <th class="px-6 py-4 text-left font-semibold">Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentFundingRequests as $funding)
                            <tr class="table-row">
                                <td class="px-6 py-4 font-mono text-xs text-slate-700 dark:text-slate-300">{{ $funding->reference }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-950 dark:text-slate-50">NGN {{ number_format((float) $funding->amount, 2) }}</td>
                                <td class="px-6 py-4">
                                    <x-ui.status-badge
                                        :value="$funding->status"
                                        :tone="match ($funding->status) {
                                            'success' => 'success',
                                            'failed' => 'danger',
                                            default => 'warning',
                                        }"
                                    />
                                </td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $funding->created_at?->format('M d, Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr class="table-row">
                                <td colspan="4" class="px-6 py-8 text-center text-slate-500 dark:text-slate-400">No funding requests have been started yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="table-shell">
                <div class="px-6 py-5">
                    <p class="section-kicker">Ledger</p>
                    <h3 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">Recent wallet movements</h3>
                </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="table-header">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Type</th>
                            <th class="px-6 py-4 text-left font-semibold">Amount</th>
                            <th class="px-6 py-4 text-left font-semibold">Reference</th>
                            <th class="px-6 py-4 text-left font-semibold">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentTransactions as $transaction)
                            <tr class="table-row">
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
                                <td class="px-6 py-4 font-mono text-xs text-slate-700 dark:text-slate-300">{{ $transaction->reference }}</td>
                                <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $transaction->created_at?->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr class="table-row">
                                <td colspan="4" class="px-6 py-8 text-center text-slate-500 dark:text-slate-400">No wallet transactions yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</x-layouts.dashboard-user>
