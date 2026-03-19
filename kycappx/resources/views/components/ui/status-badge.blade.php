@props(['value', 'tone' => 'slate'])

@php
    $tones = [
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/50 dark:text-emerald-200',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/50 dark:text-amber-200',
        'danger' => 'border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-900/60 dark:bg-rose-950/50 dark:text-rose-200',
        'info' => 'border-sky-200 bg-sky-50 text-sky-800 dark:border-sky-900/60 dark:bg-sky-950/50 dark:text-sky-200',
        'slate' => 'border-slate-200 bg-slate-100 text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200',
    ];

    $label = ucwords(str_replace(['_', '-'], ' ', (string) $value));
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold '.$tones[$tone]]) }}>
    {{ $label }}
</span>
