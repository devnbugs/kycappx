@props(['active' => false])

@php
$classes = $active
    ? 'block rounded-2xl bg-white px-4 py-3 text-sm font-semibold text-slate-950 shadow-sm'
    : 'block rounded-2xl px-4 py-3 text-sm font-medium text-slate-300 transition hover:bg-white/10 hover:text-white';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
