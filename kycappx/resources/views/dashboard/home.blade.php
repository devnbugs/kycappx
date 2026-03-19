<x-layouts.dashboard-user title="Dashboard" header="Command Center">
    <section class="grid gap-4 xl:grid-cols-[1.3fr,0.7fr]">
        <div class="surface-card relative overflow-hidden bg-slate-950 p-8 text-white">
            <div class="absolute -right-10 top-0 h-48 w-48 rounded-full bg-amber-300/20 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 h-52 w-52 rounded-full bg-teal-400/20 blur-3xl"></div>

            <div class="relative">
                <p class="section-kicker !text-teal-200">Daily Ops</p>
                <h2 class="mt-3 text-3xl font-semibold text-balance">Everything that needs attention is visible in one pass.</h2>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-200/80">
                    Track wallet balance, recent verification activity, and API readiness without jumping between disconnected pages.
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('verifications.create') }}">
                        <x-ui.button>Run Verification</x-ui.button>
                    </a>
                    <a href="{{ route('wallet') }}">
                        <x-ui.button variant="secondary">Fund Wallet</x-ui.button>
                    </a>
                </div>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
            <div class="metric-card">
                <div class="text-sm text-slate-500">Wallet balance</div>
                <div class="mt-3 text-3xl font-semibold text-slate-950">NGN {{ number_format($stats['wallet_balance'], 2) }}</div>
                <div class="mt-2 text-sm text-slate-600">Current wallet status: {{ ucfirst($wallet->status) }}</div>
            </div>

            <div class="metric-card">
                <div class="text-sm text-slate-500">Transactions</div>
                <div class="mt-3 text-3xl font-semibold text-slate-950">{{ number_format($stats['transaction_count']) }}</div>
                <div class="mt-2 text-sm text-slate-600">Ledger entries connected to your wallet.</div>
            </div>

            <div class="metric-card">
                <div class="text-sm text-slate-500">Verifications</div>
                <div class="mt-3 text-3xl font-semibold text-slate-950">{{ number_format($stats['verification_count']) }}</div>
                <div class="mt-2 text-sm text-slate-600">Submitted verification requests across all services.</div>
            </div>

            <div class="metric-card">
                <div class="text-sm text-slate-500">Active API keys</div>
                <div class="mt-3 text-3xl font-semibold text-slate-950">{{ number_format($stats['active_api_keys']) }}</div>
                <div class="mt-2 text-sm text-slate-600">Keys that can still access your account.</div>
            </div>
        </div>
    </section>

    <section class="grid gap-4 lg:grid-cols-[0.9fr,1.1fr]">
        <div class="surface-card p-6">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="section-kicker">Service Catalog</p>
                    <h3 class="mt-3 text-2xl font-semibold text-slate-950">Active verification services</h3>
                </div>
                <a href="{{ route('verifications.create') }}">
                    <x-ui.button variant="secondary">New Request</x-ui.button>
                </a>
            </div>

            <div class="mt-6 space-y-3">
                @forelse ($activeServices as $service)
                    <div class="rounded-[1.5rem] border border-slate-200/80 bg-white/80 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="text-lg font-semibold text-slate-950">{{ $service->name }}</div>
                                <div class="mt-1 text-sm text-slate-500">{{ $service->code }} · {{ strtoupper($service->country) }}</div>
                            </div>
                            <x-ui.status-badge :value="$service->is_active ? 'Active' : 'Inactive'" :tone="$service->is_active ? 'success' : 'warning'" />
                        </div>
                        <div class="mt-4 flex flex-wrap gap-2 text-xs text-slate-600">
                            <span class="badge-soft">Sell price: NGN {{ number_format((float) $service->default_price, 2) }}</span>
                            <span class="badge-soft">Cost: NGN {{ number_format((float) $service->default_cost, 2) }}</span>
                            <span class="badge-soft">{{ count($service->required_fields ?? []) }} input fields</span>
                        </div>
                    </div>
                @empty
                    <div class="rounded-[1.5rem] border border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-sm text-slate-500">
                        No verification services have been activated yet.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="table-shell">
            <div class="flex items-center justify-between gap-3 px-6 py-5">
                <div>
                    <p class="section-kicker">Recent Verification Activity</p>
                    <h3 class="mt-3 text-2xl font-semibold text-slate-950">Latest requests</h3>
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
                                    <div class="font-semibold text-slate-950">{{ $verification->service?->name ?? 'Unknown service' }}</div>
                                    <div class="text-xs text-slate-500">{{ $verification->provider_used ?: 'Awaiting provider' }}</div>
                                </td>
                                <td class="px-6 py-4 font-mono text-xs text-slate-700">{{ $verification->reference }}</td>
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
                                <td class="px-6 py-4 text-slate-600">{{ $verification->created_at?->format('M d, Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr class="table-row">
                                <td colspan="4" class="px-6 py-8 text-center text-slate-500">No verification requests have been submitted yet.</td>
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
                <h3 class="mt-3 text-2xl font-semibold text-slate-950">Latest transactions</h3>
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
                            <td class="px-6 py-4 font-mono text-xs text-slate-700">{{ $transaction->reference }}</td>
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
                            <td class="px-6 py-4 text-slate-600">{{ $transaction->source ?: 'Internal' }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $transaction->created_at?->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr class="table-row">
                            <td colspan="5" class="px-6 py-8 text-center text-slate-500">No wallet activity recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.dashboard-user>
