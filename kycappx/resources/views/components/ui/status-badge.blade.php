@props(['value', 'tone' => 'slate'])

@php
    $tones = [
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
        'warning' => 'border-amber-200 bg-amber-50 text-amber-800',
        'danger' => 'border-rose-200 bg-rose-50 text-rose-800',
        'info' => 'border-sky-200 bg-sky-50 text-sky-800',
        'slate' => 'border-slate-200 bg-slate-100 text-slate-700',
    ];

    $label = ucwords(str_replace(['_', '-'], ' ', (string) $value));
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold '.$tones[$tone]]) }}>
    {{ $label }}
</span>
