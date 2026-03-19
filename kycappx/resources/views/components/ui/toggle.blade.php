@props([
    'name',
    'checked' => false,
    'label' => null,
    'description' => null,
    'value' => '1',
    'showHidden' => true,
])

<label class="flex items-start justify-between gap-4 rounded-[1.25rem] border px-4 py-4" style="border-color: var(--ui-border); background: var(--ui-panel-strong);">
    <span class="min-w-0">
        @if ($label)
            <span class="block text-sm font-semibold text-slate-950 dark:text-slate-50">{{ $label }}</span>
        @endif

        @if ($description)
            <span class="mt-1 block text-xs leading-5 text-slate-500 dark:text-slate-300">{{ $description }}</span>
        @endif
    </span>

    <span class="relative inline-flex h-7 w-12 shrink-0">
        @if ($showHidden)
            <input type="hidden" name="{{ $name }}" value="0">
        @endif

        <input
            type="checkbox"
            name="{{ $name }}"
            value="{{ $value }}"
            @checked($checked)
            {{ $attributes->merge(['class' => 'peer sr-only']) }}
        >

        <span class="absolute inset-0 rounded-full border border-transparent bg-slate-300/80 transition peer-checked:bg-teal-500 dark:bg-slate-700/90 dark:peer-checked:bg-teal-400"></span>
        <span class="absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"></span>
    </span>
</label>
