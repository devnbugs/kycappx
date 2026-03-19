<x-layouts.dashboard-user title="Wallet" header="Wallet Operations">
    <section class="grid gap-4 xl:grid-cols-[0.9fr,1.1fr]">
        <div class="surface-card relative overflow-hidden bg-slate-950 p-8 text-white">
            <div class="absolute -right-10 top-0 h-48 w-48 rounded-full bg-amber-300/20 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 h-52 w-52 rounded-full bg-teal-400/20 blur-3xl"></div>

            <div class="relative">
                <p class="section-kicker !text-teal-200">Available Balance</p>
                <div class="mt-4 text-5xl font-semibold">NGN {{ number_format((float) $wallet->balance, 2) }}</div>
                <div class="mt-3 text-sm text-slate-200/75">
                    Wallet status: {{ ucfirst($wallet->status) }} · Currency: {{ strtoupper($wallet->currency) }}
                </div>

                <div class="mt-8 flex flex-wrap gap-2 text-xs">
                    <span class="badge-soft border-white/10 bg-white/10 text-white">Kora {{ $gatewayStatus['kora'] ? 'configured' : 'not configured' }}</span>
                    <span class="badge-soft border-white/10 bg-white/10 text-white">Paystack coming next</span>
                </div>
            </div>
        </div>

        <div class="surface-card p-6 sm:p-8">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="section-kicker">Fund Wallet</p>
                    <h2 class="mt-3 text-2xl font-semibold text-slate-950">Initialize a new Kora top-up</h2>
                </div>
                <x-ui.status-badge :value="$gatewayStatus['kora'] ? 'Live' : 'Setup Required'" :tone="$gatewayStatus['kora'] ? 'success' : 'warning'" />
            </div>

            <p class="mt-3 text-sm leading-6 text-slate-600">
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
                    @if ($gatewayStatus['kora'])
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

            @unless ($gatewayStatus['kora'])
                <div class="mt-5 rounded-[1.5rem] border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-900">
                    Add `KORA_SECRET_KEY` and `KORA_REDIRECT_URL` to enable production wallet funding.
                </div>
            @endunless
        </div>
    </section>

    <section class="grid gap-4 lg:grid-cols-2">
        <div class="table-shell">
            <div class="px-6 py-5">
                <p class="section-kicker">Funding Requests</p>
                <h3 class="mt-3 text-2xl font-semibold text-slate-950">Latest top-up attempts</h3>
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
                                <td class="px-6 py-4 font-mono text-xs text-slate-700">{{ $funding->reference }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-950">NGN {{ number_format((float) $funding->amount, 2) }}</td>
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
                                <td class="px-6 py-4 text-slate-600">{{ $funding->created_at?->format('M d, Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr class="table-row">
                                <td colspan="4" class="px-6 py-8 text-center text-slate-500">No funding requests have been started yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="table-shell">
            <div class="px-6 py-5">
                <p class="section-kicker">Ledger</p>
                <h3 class="mt-3 text-2xl font-semibold text-slate-950">Recent wallet movements</h3>
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
                                <td class="px-6 py-4 font-semibold text-slate-950">NGN {{ number_format((float) $transaction->amount, 2) }}</td>
                                <td class="px-6 py-4 font-mono text-xs text-slate-700">{{ $transaction->reference }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $transaction->created_at?->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr class="table-row">
                                <td colspan="4" class="px-6 py-8 text-center text-slate-500">No wallet transactions yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</x-layouts.dashboard-user>
