<x-layouts.dashboard-user title="Verifications" header="Verification Requests">
    <section class="surface-card p-6 sm:p-8">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="section-kicker">Verification Queue</p>
                <h2 class="mt-3 text-2xl font-semibold text-slate-950">Track every KYC and KYB request</h2>
                <p class="mt-3 text-sm leading-6 text-slate-600">
                    Your current available balance is <span class="font-semibold text-slate-950">NGN {{ number_format((float) $wallet->balance, 2) }}</span>. Successful automated checks bill against this wallet with a unique reference.
                </p>
            </div>

            <a href="{{ route('verifications.create') }}">
                <x-ui.button>New Verification</x-ui.button>
            </a>
        </div>
    </section>

    <section class="table-shell">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="table-header">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold">Service</th>
                        <th class="px-6 py-4 text-left font-semibold">Reference</th>
                        <th class="px-6 py-4 text-left font-semibold">Status</th>
                        <th class="px-6 py-4 text-left font-semibold">Provider</th>
                        <th class="px-6 py-4 text-left font-semibold">Price</th>
                        <th class="px-6 py-4 text-left font-semibold">Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($verifications as $verification)
                        <tr class="table-row">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-950">{{ $verification->service?->name ?? 'Unknown service' }}</div>
                                <div class="text-xs text-slate-500">{{ $verification->service?->code ?? 'N/A' }}</div>
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
                            <td class="px-6 py-4 text-slate-600">{{ $verification->provider_used ?: 'Awaiting provider' }}</td>
                            <td class="px-6 py-4 font-semibold text-slate-950">NGN {{ number_format((float) $verification->customer_price, 2) }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $verification->created_at?->format('M d, Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr class="table-row">
                            <td colspan="6" class="px-6 py-10 text-center text-slate-500">No verification requests have been submitted yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200/80 px-6 py-5">
            {{ $verifications->links() }}
        </div>
    </section>
</x-layouts.dashboard-user>
