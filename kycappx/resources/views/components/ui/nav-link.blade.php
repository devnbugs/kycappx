@props(['active' => false])

@php
$classes = $active
    ? 'block px-3 py-2 rounded-lg bg-gray-100 text-gray-900 font-medium'
    : 'block px-3 py-2 rounded-lg text-gray-700 hover:bg-gray-50 hover:text-gray-900';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>