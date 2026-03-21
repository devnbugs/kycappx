<x-layouts.dashboard-admin title="Verification Logs" header="Verification Processing Logs">
    <section class="surface-card p-6 sm:p-8">
        <p class="section-kicker">Verification Feed</p>
        <h2 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">Provider outcomes, customer pricing, and request status</h2>
        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
            Review how verification requests are moving through the system and identify requests that may need manual follow-up.
        </p>
    </section>

    <section class="table-shell">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="table-header">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold">Customer</th>
                        <th class="px-6 py-4 text-left font-semibold">Service</th>
                        <th class="px-6 py-4 text-left font-semibold">Reference</th>
                        <th class="px-6 py-4 text-left font-semibold">Provider</th>
                        <th class="px-6 py-4 text-left font-semibold">Price</th>
                        <th class="px-6 py-4 text-left font-semibold">Status</th>
                        <th class="px-6 py-4 text-left font-semibold">Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($verifications as $verification)
                        <tr class="table-row">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-950 dark:text-slate-50">{{ $verification->user?->name ?? 'Unknown user' }}</div>
                                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $verification->user?->email }}</div>
                            </td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $verification->service?->name ?? 'Unknown service' }}</td>
                            <td class="px-6 py-4 font-mono text-xs text-slate-700 dark:text-slate-300">{{ $verification->reference }}</td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $verification->provider_used ?: 'Awaiting provider' }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-950 dark:text-slate-50">NGN {{ number_format((float) $verification->customer_price, 2) }}</td>
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
                            <td colspan="7" class="px-6 py-10 text-center text-slate-500 dark:text-slate-400">No verification logs have been recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200/80 px-6 py-5 dark:border-slate-800">
            {{ $verifications->links() }}
        </div>
    </section>
</x-layouts.dashboard-admin>
