@props([
    'services' => collect(),
    'title' => 'Quick Verification',
    'copy' => 'Jump straight into the most-used checks from this page.',
    'buttonLabel' => 'Open Full Catalog',
])

<section class="surface-card p-6 sm:p-8">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <p class="section-kicker">Verification</p>
            <h2 class="mt-3 text-2xl font-semibold text-slate-950 dark:text-slate-50">{{ $title }}</h2>
            <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                {{ $copy }}
            </p>
        </div>
        <a href="{{ route('verifications.create') }}">
            <x-ui.button variant="secondary">{{ $buttonLabel }}</x-ui.button>
        </a>
    </div>

    <div class="mt-6 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
        @forelse (collect($services)->take(6) as $service)
            <a href="{{ route('verifications.create', ['service' => $service->id]) }}" class="rounded-[1.35rem] border border-slate-200/80 bg-white/80 p-4 transition hover:-translate-y-0.5 hover:border-slate-300 hover:bg-white dark:border-slate-700 dark:bg-slate-900/70 dark:hover:border-slate-600">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-base font-semibold text-slate-950 dark:text-slate-50">{{ $service->name }}</div>
                        <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $service->code }} · {{ strtoupper($service->country) }}</div>
                    </div>
                    <span class="badge-soft">NGN {{ number_format((float) $service->default_price, 2) }}</span>
                </div>
            </a>
        @empty
            <div class="rounded-[1.35rem] border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-300 md:col-span-2 xl:col-span-3">
                No launchable verification services are active right now.
            </div>
        @endforelse
    </div>
</section>
