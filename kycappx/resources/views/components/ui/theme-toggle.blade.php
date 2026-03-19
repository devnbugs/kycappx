@if (($siteSettings->dark_mode_enabled ?? true))
    <button
        type="button"
        x-data="{ theme: document.documentElement.dataset.theme || @js(auth()->user()?->theme_preference ?? ($siteSettings->default_theme ?? 'system')) }"
        x-on:theme-changed.window="theme = $event.detail.theme"
        onclick="window.toggleTheme()"
        {{ $attributes->merge(['class' => 'theme-toggle']) }}
    >
        <span class="theme-toggle__hint">Theme</span>
        <span x-text="theme === 'dark' ? 'Dark' : (theme === 'light' ? 'Light' : 'Auto')"></span>
    </button>
@endif
