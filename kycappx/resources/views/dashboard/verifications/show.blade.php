@php
    $linkPrimary = 'inline-flex items-center justify-center gap-2 rounded-full bg-slate-950 px-5 py-2.5 text-sm font-semibold text-white transition hover:-translate-y-0.5 hover:bg-slate-800 dark:bg-teal-500 dark:text-slate-950 dark:hover:bg-teal-400';
    $linkSecondary = 'inline-flex items-center justify-center gap-2 rounded-full border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-800 transition hover:-translate-y-0.5 hover:border-slate-300 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:border-slate-600 dark:hover:bg-slate-800';
@endphp

<x-layouts.dashboard-user :title="$report['serviceName']" header="Verification Response">
    @if (session('status'))
        <section class="surface-card p-5">
            <div class="rounded-[1.25rem] border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        </section>
    @endif

    <section class="surface-card p-6 sm:p-8">
        <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
            <div>
                <p class="section-kicker">Saved Verification Output</p>
                <h2 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">{{ $report['serviceName'] }}</h2>
                <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-300">{{ $report['message'] }}</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="badge-soft">{{ $report['serviceCode'] }}</span>
                    <span class="badge-soft">{{ $report['reference'] }}</span>
                    @if ($report['providerVersion'])
                        <span class="badge-soft">{{ $report['providerVersion'] }}</span>
                    @endif
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('verifications.download', ['verificationRequest' => $verification->id, 'mode' => $report['printModes'][0]]) }}" class="{{ $linkSecondary }}">
                    Download
                </a>
                <a href="{{ route('verifications.print', ['verificationRequest' => $verification->id, 'mode' => 'standard']) }}" target="_blank" rel="noopener" class="{{ $linkPrimary }}">
                    Print Standard
                </a>
                @if (in_array('premium', $report['printModes'], true))
                    <a href="{{ route('verifications.print', ['verificationRequest' => $verification->id, 'mode' => 'premium']) }}" target="_blank" rel="noopener" class="{{ $linkSecondary }}">
                        Print Premium
                    </a>
                @endif
            </div>
        </div>
    </section>

    <section class="grid gap-4 xl:grid-cols-[1.08fr,0.92fr]">
        <div class="surface-card p-6 sm:p-8">
            <p class="section-kicker">Response Summary</p>
            <div class="mt-5">
                <x-verifications.item-grid :items="$report['summaryItems']" />
            </div>
        </div>

        <div class="surface-card p-6 sm:p-8">
            <p class="section-kicker">Response Meta</p>
            <div class="mt-5">
                <x-verifications.item-grid :items="$report['metaItems']" />
            </div>

            @if ($report['photo'] || $report['signature'])
                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    @if ($report['photo'])
                        <div class="rounded-[1.35rem] border border-slate-200/80 bg-slate-50/80 p-4 dark:border-slate-700 dark:bg-slate-900/60">
                            <div class="text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-slate-500 dark:text-slate-400">Photo</div>
                            <img src="{{ $report['photo'] }}" alt="Verification subject photo" class="mt-3 h-44 w-full rounded-[1.1rem] object-cover">
                        </div>
                    @endif
                    @if ($report['signature'])
                        <div class="rounded-[1.35rem] border border-slate-200/80 bg-slate-50/80 p-4 dark:border-slate-700 dark:bg-slate-900/60">
                            <div class="text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-slate-500 dark:text-slate-400">Signature</div>
                            <img src="{{ $report['signature'] }}" alt="Verification subject signature" class="mt-3 h-44 w-full rounded-[1.1rem] object-contain bg-white p-4 dark:bg-slate-950">
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
        <div class="surface-card p-6 sm:p-8">
            <p class="section-kicker">Normalized Response</p>
            <div class="mt-5">
                <x-verifications.item-grid :items="$report['detailItems']" empty="No structured response fields were saved for this request." />
            </div>
        </div>

        <div class="surface-card p-6 sm:p-8">
            <p class="section-kicker">Request Payload</p>
            <div class="mt-5">
                <x-verifications.item-grid :items="$report['requestItems']" empty="No request payload was preserved for this verification." />
            </div>
        </div>
    </section>

    <section class="surface-card p-6 sm:p-8">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="section-kicker">Engine Attempts</p>
                <h2 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">Processing trail</h2>
            </div>
            <x-ui.status-badge :value="$report['statusLabel']" :tone="$report['statusTone']" />
        </div>

        <div class="mt-6 space-y-3">
            @forelse ($report['attempts'] as $attempt)
                <div class="rounded-[1.35rem] border border-slate-200/80 bg-slate-50/80 p-4 dark:border-slate-700 dark:bg-slate-900/60">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <div class="text-sm font-semibold text-slate-950 dark:text-slate-50">
                                {{ $attempt['engine'] }}
                                @if ($attempt['providerName'] && $attempt['providerName'] !== $attempt['engine'])
                                    <span class="text-xs text-slate-400 dark:text-slate-500">· {{ $attempt['providerName'] }}</span>
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
    </section>

    <section class="surface-card p-6 sm:p-8">
        <p class="section-kicker">Raw Response</p>
        <details class="mt-5 rounded-[1.35rem] border border-slate-200/80 bg-slate-50/80 p-4 dark:border-slate-700 dark:bg-slate-900/60">
            <summary class="cursor-pointer text-sm font-semibold text-slate-950 dark:text-slate-50">Expand raw provider payload</summary>
            <pre class="mt-4 overflow-x-auto whitespace-pre-wrap break-all rounded-[1rem] bg-slate-950/95 p-4 text-xs leading-6 text-slate-100">{{ $report['rawJson'] }}</pre>
        </details>
    </section>
</x-layouts.dashboard-user>
