@props(['variant' => 'primary', 'type' => 'button'])

@php
$base = 'inline-flex items-center justify-center gap-2 rounded-full px-5 py-2.5 text-sm font-semibold transition duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60';
$variants = [
  'primary' => 'bg-slate-950 text-white hover:-translate-y-0.5 hover:bg-slate-800 focus:ring-slate-900',
  'secondary' => 'border border-slate-200 bg-white text-slate-800 hover:-translate-y-0.5 hover:border-slate-300 hover:bg-slate-50 focus:ring-slate-300',
  'danger' => 'bg-rose-600 text-white hover:-translate-y-0.5 hover:bg-rose-500 focus:ring-rose-500',
];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $base.' '.$variants[$variant]]) }}>
    {{ $slot }}
</button>
