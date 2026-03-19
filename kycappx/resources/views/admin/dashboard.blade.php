<x-layouts.dashboard-admin title="Admin Overview" header="Operations Overview">
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <div class="metric-card">
            <div class="text-sm text-slate-500">Users</div>
            <div class="mt-3 text-3xl font-semibold text-slate-950">{{ number_format($metrics['users']) }}</div>
            <div class="mt-2 text-sm text-slate-600">{{ number_format($metrics['customers']) }} customer accounts are active in the workspace.</div>
        </div>

        <div class="metric-card">
            <div class="text-sm text-slate-500">Verifications</div>
            <div class="mt-3 text-3xl font-semibold text-slate-950">{{ number_format($metrics['verifications']) }}</div>
            <div class="mt-2 text-sm text-slate-600">{{ number_format($metrics['successful_verifications']) }} have completed successfully.</div>
        </div>

        <div class="metric-card">
            <div class="text-sm text-slate-500">Webhooks</div>
            <div class="mt-3 text-3xl font-semibold text-slate-950">{{ number_format($metrics['webhooks']) }}</div>
            <div class="mt-2 text-sm text-slate-600">{{ number_format($metrics['failed_webhooks']) }} still need attention.</div>
        </div>
    </section>

    <section class="grid gap-4 xl:grid-cols-[0.95fr,1.05fr]">
        <div class="table-shell">
            <div class="flex items-center justify-between gap-3 px-6 py-5">
                <div>
                    <p class="section-kicker">Recent Customers</p>
                    <h2 class="mt-3 text-2xl font-semibold text-slate-950">Newest accounts</h2>
                </div>
                <a href="{{ route('admin.customers.index') }}">
                    <x-ui.button variant="secondary">View All</x-ui.button>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="table-header">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Customer</th>
                            <th class="px-6 py-4 text-left font-semibold">Role</th>
                            <th class="px-6 py-4 text-left font-semibold">Wallet</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentCustomers as $customer)
                            <tr class="table-row">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-950">{{ $customer->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $customer->email }}</div>
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $customer->getRoleNames()->implode(', ') ?: 'Unassigned' }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-950">NGN {{ number_format((float) ($customer->wallet?->balance ?? 0), 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="table-shell">
            <div class="flex items-center justify-between gap-3 px-6 py-5">
                <div>
                    <p class="section-kicker">Recent Webhooks</p>
                    <h2 class="mt-3 text-2xl font-semibold text-slate-950">Latest delivery events</h2>
                </div>
                <a href="{{ route('admin.logs.webhooks') }}">
                    <x-ui.button variant="secondary">Open Logs</x-ui.button>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="table-header">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Provider</th>
                            <th class="px-6 py-4 text-left font-semibold">Reference</th>
                            <th class="px-6 py-4 text-left font-semibold">Processed</th>
                            <th class="px-6 py-4 text-left font-semibold">Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentWebhooks as $log)
                            <tr class="table-row">
                                <td class="px-6 py-4 font-semibold text-slate-950">{{ strtoupper($log->provider) }}</td>
                                <td class="px-6 py-4 font-mono text-xs text-slate-700">{{ $log->reference ?: 'N/A' }}</td>
                                <td class="px-6 py-4">
                                    <x-ui.status-badge :value="$log->processed ? 'Processed' : 'Pending'" :tone="$log->processed ? 'success' : 'warning'" />
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $log->created_at?->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section class="table-shell">
        <div class="flex items-center justify-between gap-3 px-6 py-5">
            <div>
                <p class="section-kicker">Verification Feed</p>
                <h2 class="mt-3 text-2xl font-semibold text-slate-950">Latest verification requests</h2>
            </div>
            <a href="{{ route('admin.logs.verifications') }}">
                <x-ui.button variant="secondary">Open Queue</x-ui.button>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="table-header">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold">Customer</th>
                        <th class="px-6 py-4 text-left font-semibold">Service</th>
                        <th class="px-6 py-4 text-left font-semibold">Reference</th>
                        <th class="px-6 py-4 text-left font-semibold">Status</th>
                        <th class="px-6 py-4 text-left font-semibold">Time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recentVerifications as $verification)
                        <tr class="table-row">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-950">{{ $verification->user?->name ?? 'Unknown user' }}</div>
                                <div class="text-xs text-slate-500">{{ $verification->user?->email }}</div>
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $verification->service?->name ?? 'Unknown service' }}</td>
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
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.dashboard-admin>
