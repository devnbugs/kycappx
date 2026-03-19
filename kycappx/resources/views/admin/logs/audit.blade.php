<x-layouts.dashboard-admin title="Audit Logs" header="Admin Audit Trail">
    <section class="surface-card p-6 sm:p-8">
        <p class="section-kicker">Operational History</p>
        <h2 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">Track every admin mutation across the platform</h2>
        <p class="mt-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
            This log records user-management changes, service updates, provider controls, and site-setting changes so you can trace who changed what and when.
        </p>
    </section>

    <section class="table-shell">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="table-header">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold">Action</th>
                        <th class="px-6 py-4 text-left font-semibold">Actor</th>
                        <th class="px-6 py-4 text-left font-semibold">Target</th>
                        <th class="px-6 py-4 text-left font-semibold">Meta</th>
                        <th class="px-6 py-4 text-left font-semibold">Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr class="table-row">
                            <td class="px-6 py-4 font-semibold text-slate-950 dark:text-slate-50">{{ str($log->action)->replace('.', ' ')->headline() }}</td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $log->user?->name ?? 'System' }}</td>
                            <td class="px-6 py-4 font-mono text-xs text-slate-700 dark:text-slate-300">{{ class_basename((string) $log->target_type) }}#{{ $log->target_id }}</td>
                            <td class="px-6 py-4 text-xs text-slate-600 dark:text-slate-300">{{ json_encode($log->meta) }}</td>
                            <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $log->created_at?->format('M d, Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr class="table-row">
                            <td colspan="5" class="px-6 py-10 text-center text-slate-500 dark:text-slate-400">No audit entries have been recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200/80 px-6 py-5 dark:border-slate-800">
            {{ $logs->links() }}
        </div>
    </section>
</x-layouts.dashboard-admin>
