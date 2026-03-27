@props([
    'items' => [],
    'columns' => 'sm:grid-cols-2',
    'empty' => 'No values are available yet.',
])

<div @class(['grid gap-3', $columns])>
    @forelse ($items as $item)
        <div class="rounded-[1.35rem] border border-slate-200/80 bg-slate-50/80 px-4 py-4 dark:border-slate-700 dark:bg-slate-900/60">
            <div class="text-[0.68rem] font-semibold uppercase tracking-[0.24em] text-slate-500 dark:text-slate-400">{{ $item['label'] }}</div>
            <div class="mt-2 break-words text-sm font-semibold text-slate-950 dark:text-slate-50">{{ $item['value'] }}</div>
        </div>
    @empty
        <div class="rounded-[1.35rem] border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-300">
            {{ $empty }}
        </div>
    @endforelse
</div>
