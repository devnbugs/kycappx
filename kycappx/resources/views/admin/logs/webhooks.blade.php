<x-layouts.dashboard-admin title="Webhook Logs" header="Webhook Delivery Logs">
    <section class="surface-card p-6 sm:p-8">
        <p class="section-kicker">Inbound Events</p>
        <h2 class="mt-3 text-2xl font-semibold text-slate-950">Signed callbacks, references, and processing status</h2>
        <p class="mt-3 text-sm leading-6 text-slate-600">
            Use this view to validate provider callbacks, inspect payload handling, and spot references that still need reconciliation.
        </p>
    </section>

    <section class="table-shell">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="table-header">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold">Provider</th>
                        <th class="px-6 py-4 text-left font-semibold">Event</th>
                        <th class="px-6 py-4 text-left font-semibold">Reference</th>
                        <th class="px-6 py-4 text-left font-semibold">Signature</th>
                        <th class="px-6 py-4 text-left font-semibold">Processed</th>
                        <th class="px-6 py-4 text-left font-semibold">Error</th>
                        <th class="px-6 py-4 text-left font-semibold">Received</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr class="table-row">
                            <td class="px-6 py-4 font-semibold text-slate-950">{{ strtoupper($log->provider) }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $log->event ?: 'Unknown event' }}</td>
                            <td class="px-6 py-4 font-mono text-xs text-slate-700">{{ $log->reference ?: 'N/A' }}</td>
                            <td class="px-6 py-4">
                                <x-ui.status-badge :value="$log->signature_valid ? 'Valid' : 'Invalid'" :tone="$log->signature_valid ? 'success' : 'danger'" />
                            </td>
                            <td class="px-6 py-4">
                                <x-ui.status-badge :value="$log->processed ? 'Processed' : 'Pending'" :tone="$log->processed ? 'success' : 'warning'" />
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $log->error_message ?: 'No error' }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $log->created_at?->format('M d, Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr class="table-row">
                            <td colspan="7" class="px-6 py-10 text-center text-slate-500">No webhook events have been captured yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200/80 px-6 py-5">
            {{ $logs->links() }}
        </div>
    </section>
</x-layouts.dashboard-admin>
