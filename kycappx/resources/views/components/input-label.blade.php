@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-semibold text-slate-800 dark:text-slate-100']) }}>
    {{ $value ?? $slot }}
</label>
