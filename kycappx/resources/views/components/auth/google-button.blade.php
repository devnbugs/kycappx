@props([
    'href' => '#',
    'label' => 'Continue with Google',
    'fullWidth' => true,
])

<a
    href="{{ $href }}"
    {{ $attributes->merge([
        'class' => 'inline-flex items-center justify-center gap-3 rounded-2xl border border-slate-950 bg-slate-950 px-5 py-3 text-sm font-semibold text-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2 dark:border-white dark:bg-white dark:text-slate-950 dark:hover:bg-slate-100 dark:focus:ring-white '.($fullWidth ? 'w-full' : ''),
    ]) }}
>
    <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-white shadow-sm dark:bg-slate-950">
        <svg viewBox="0 0 24 24" class="h-4 w-4" aria-hidden="true">
            <path fill="#EA4335" d="M12.24 10.285V14.4h5.88c-.257 1.322-1.54 3.879-5.88 3.879-3.537 0-6.42-2.927-6.42-6.539S8.703 5.2 12.24 5.2c2.015 0 3.366.86 4.14 1.602l2.82-2.73C17.4 2.395 15.06 1.2 12.24 1.2 6.15 1.2 1.2 6.15 1.2 12.24s4.95 11.04 11.04 11.04c6.372 0 10.602-4.477 10.602-10.79 0-.726-.08-1.278-.177-1.835z"/>
            <path fill="#34A853" d="M1.2 6.054l3.383 2.48c.916-1.817 2.798-3.334 5.657-3.334 2.015 0 3.366.86 4.14 1.602l2.82-2.73C15.06 2.395 12.72 1.2 9.9 1.2 5.66 1.2 2.014 3.618 1.2 6.054z"/>
            <path fill="#FBBC05" d="M1.2 18.426l3.89-2.994c.985 1.94 2.79 2.847 5.15 2.847 4.084 0 5.515-2.557 5.88-3.879h-5.88v-4.115h10.425c.111.58.177 1.202.177 1.835 0 6.313-4.23 10.79-10.602 10.79-4.232 0-7.833-2.39-9.04-5.484z"/>
            <path fill="#4285F4" d="M22.842 12.49c0-.726-.08-1.278-.177-1.835H12.24v4.115h5.88c-.28 1.36-1.66 4.079-5.88 4.079-3.537 0-6.42-2.927-6.42-6.539 0-3.612 2.883-6.539 6.42-6.539 2.015 0 3.366.86 4.14 1.602l2.82-2.73C17.4 2.395 15.06 1.2 12.24 1.2 6.15 1.2 1.2 6.15 1.2 12.24s4.95 11.04 11.04 11.04c6.372 0 10.602-4.477 10.602-10.79z"/>
        </svg>
    </span>

    <span>{{ $label }}</span>
</a>
