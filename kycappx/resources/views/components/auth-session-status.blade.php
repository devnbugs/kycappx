@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900 dark:border-emerald-900/60 dark:bg-emerald-950/50 dark:text-emerald-200']) }}>
        {{ $status }}
    </div>
@endif
