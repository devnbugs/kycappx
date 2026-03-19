<x-layouts.dashboard-user title="Transactions" header="Wallet Ledger">
    <section class="surface-card p-6 sm:p-8">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="section-kicker">Ledger View</p>
                <h2 class="mt-3 text-2xl font-semibold text-slate-950">Filter and inspect wallet activity</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    Every transaction is recorded against your wallet with a stable reference for audit and reconciliation.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                @foreach (['all' => 'All', 'credit' => 'Credits', 'debit' => 'Debits', 'refund' => 'Refunds'] as $filter => $label)
                    <a
                        href="{{ route('transactions', ['type' => $filter]) }}"
                        @class([
                            'rounded-full px-4 py-2 text-sm font-semibold transition',
                            'bg-slate-950 text-white' => $type === $filter,
                            'bg-slate-100 text-slate-700 hover:bg-slate-200' => $type !== $filter,
                        ])
                    >
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="table-shell">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="table-header">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold">Reference</th>
                        <th class="px-6 py-4 text-left font-semibold">Type</th>
                        <th class="px-6 py-4 text-left font-semibold">Amount</th>
                        <th class="px-6 py-4 text-left font-semibold">Source</th>
                        <th class="px-6 py-4 text-left font-semibold">Description</th>
                        <th class="px-6 py-4 text-left font-semibold">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $transaction)
                        <tr class="table-row">
                            <td class="px-6 py-4 font-mono text-xs text-slate-700">{{ $transaction->reference }}</td>
                            <td class="px-6 py-4">
                                <x-ui.status-badge
                                    :value="$transaction->type"
                                    :tone="match ($transaction->type) {
                                        'credit' => 'success',
                                        'debit' => 'warning',
                                        'refund' => 'info',
                                        default => 'slate',
                                    }"
                                />
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-950">NGN {{ number_format((float) $transaction->amount, 2) }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $transaction->source ?: 'Internal' }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $transaction->description ?: 'No description provided' }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $transaction->created_at?->format('M d, Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr class="table-row">
                            <td colspan="6" class="px-6 py-10 text-center text-slate-500">No transactions match this filter yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200/80 px-6 py-5">
            {{ $transactions->links() }}
        </div>
    </section>
</x-layouts.dashboard-user>
