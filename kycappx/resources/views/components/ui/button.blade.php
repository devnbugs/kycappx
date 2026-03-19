@props(['variant' => 'primary', 'type' => 'button'])

@php
$base = 'inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition border';
$variants = [
  'primary' => 'bg-gray-900 text-white border-gray-900 hover:bg-black',
  'secondary' => 'bg-white text-gray-900 border-gray-200 hover:bg-gray-50',
  'danger' => 'bg-red-600 text-white border-red-600 hover:bg-red-700',
];
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $base.' '.$variants[$variant]]) }}>
    {{ $slot }}
</button>