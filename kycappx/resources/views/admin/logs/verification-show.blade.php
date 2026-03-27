<x-layouts.dashboard-admin :title="$report['serviceName']" header="Verification Log Detail">
    <section class="surface-card p-6 sm:p-8">
        <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
            <div>
                <p class="section-kicker">Verification Audit</p>
                <h2 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">{{ $report['serviceName'] }}</h2>
                <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $report['message'] }}</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="badge-soft">{{ $report['reference'] }}</span>
                    <span class="badge-soft">{{ $report['serviceCode'] }}</span>
                    @if ($report['providerAdminLabel'])
                        <span class="badge-soft">{{ $report['providerAdminLabel'] }}</span>
                    @endif
                    @if ($report['providerVersion'])
                        <span class="badge-soft">{{ $report['providerVersion'] }}</span>
                    @endif
                </div>
            </div>

            <a href="{{ route('admin.logs.verifications') }}" class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-800 transition hover:-translate-y-0.5 hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:border-slate-600 dark:hover:bg-slate-800">
                Back to Logs
            </a>
        </div>
    </section>

    <section class="grid gap-4 xl:grid-cols-[0.9fr,1.1fr]">
        <div class="surface-card p-6 sm:p-8">
            <p class="section-kicker">Customer</p>
            <div class="mt-5 space-y-4">
                <div class="rounded-[1.35rem] border border-slate-200/80 bg-slate-50/80 px-4 py-4 dark:border-slate-700 dark:bg-slate-900/60">
                    <div class="text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-slate-500 dark:text-slate-400">Name</div>
                    <div class="mt-2 text-sm font-semibold text-slate-950 dark:text-slate-50">{{ $verification->user?->name ?? 'Unknown customer' }}</div>
                </div>
                <div class="rounded-[1.35rem] border border-slate-200/80 bg-slate-50/80 px-4 py-4 dark:border-slate-700 dark:bg-slate-900/60">
                    <div class="text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-slate-500 dark:text-slate-400">Email</div>
                    <div class="mt-2 break-words text-sm font-semibold text-slate-950 dark:text-slate-50">{{ $verification->user?->email ?? 'N/A' }}</div>
                </div>
                <div class="rounded-[1.35rem] border border-slate-200/80 bg-slate-50/80 px-4 py-4 dark:border-slate-700 dark:bg-slate-900/60">
                    <div class="text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-slate-500 dark:text-slate-400">Account</div>
                    <div class="mt-2 text-sm font-semibold text-slate-950 dark:text-slate-50">{{ '@'.($verification->user?->username ?? 'unknown') }}</div>
                </div>
            </div>
        </div>

        <div class="surface-card p-6 sm:p-8">
            <p class="section-kicker">Summary</p>
            <div class="mt-5">
                <x-verifications.item-grid :items="$report['summaryItems']" />
            </div>
        </div>
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
        <div class="surface-card p-6 sm:p-8">
            <p class="section-kicker">Response Meta</p>
            <div class="mt-5">
                <x-verifications.item-grid :items="$report['metaItems']" />
            </div>
        </div>

        <div class="surface-card p-6 sm:p-8">
            <p class="section-kicker">Normalized Response</p>
            <div class="mt-5">
                <x-verifications.item-grid :items="$report['detailItems']" empty="No structured response fields were saved for this request." />
            </div>
        </div>
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
        <div class="surface-card p-6 sm:p-8">
            <p class="section-kicker">Request Payload</p>
            <div class="mt-5">
                <x-verifications.item-grid :items="$report['requestItems']" empty="No request payload was preserved for this verification." />
            </div>
        </div>

        <div class="surface-card p-6 sm:p-8">
            <p class="section-kicker">Engine Attempts</p>
            <div class="mt-5 space-y-3">
                @forelse ($report['attempts'] as $attempt)
                    <div class="rounded-[1.35rem] border border-slate-200/80 bg-slate-50/80 p-4 dark:border-slate-700 dark:bg-slate-900/60">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <div class="text-sm font-semibold text-slate-950 dark:text-slate-50">
                                    {{ $attempt['providerName'] ?? $attempt['engine'] }}
                                    @if ($attempt['version'])
                                        <span class="text-xs text-slate-400 dark:text-slate-500">· {{ $attempt['version'] }}</span>
                                    @endif
                                </div>
                                <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                    Started {{ $attempt['startedAt'] ?? 'N/A' }} · Finished {{ $attempt['finishedAt'] ?? 'N/A' }}
                                </div>
                                @if ($attempt['error'])
                                    <div class="mt-3 text-sm text-rose-600 dark:text-rose-300">{{ $attempt['error'] }}</div>
                                @endif
                            </div>
                            <x-ui.status-badge :value="$attempt['statusLabel']" :tone="$attempt['statusTone']" />
                        </div>
                    </div>
                @empty
                    <div class="rounded-[1.35rem] border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-300">
                        No provider attempts were recorded for this request yet.
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="surface-card p-6 sm:p-8">
        <p class="section-kicker">Raw Response</p>
        <details class="mt-5 rounded-[1.35rem] border border-slate-200/80 bg-slate-50/80 p-4 dark:border-slate-700 dark:bg-slate-900/60">
            <summary class="cursor-pointer text-sm font-semibold text-slate-950 dark:text-slate-50">Expand raw provider payload</summary>
            <pre class="mt-4 overflow-x-auto whitespace-pre-wrap break-all rounded-[1rem] bg-slate-950/95 p-4 text-xs leading-6 text-slate-100">{{ $report['rawJson'] }}</pre>
        </details>
    </section>
</x-layouts.dashboard-admin>
