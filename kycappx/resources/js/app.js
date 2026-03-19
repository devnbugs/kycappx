import './bootstrap';

import Alpine from 'alpinejs';

const STORAGE_KEY = 'kycappx.theme';

const resolveIsDark = (theme) => {
    if (theme === 'dark') {
        return true;
    }

    if (theme === 'light') {
        return false;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches;
};

window.applyTheme = async (theme, options = {}) => {
    const settings = window.__theme || {};
    const persist = options.persist ?? true;
    const nextTheme = settings.enabled === false ? 'light' : theme;
    const isDark = resolveIsDark(nextTheme);

    document.documentElement.classList.toggle('dark', isDark);
    document.documentElement.dataset.theme = nextTheme;
    localStorage.setItem(STORAGE_KEY, nextTheme);
    window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme: nextTheme, isDark } }));

    if (persist && settings.persistUrl) {
        try {
            await window.fetch(settings.persistUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': settings.csrfToken,
                },
                credentials: 'same-origin',
                body: JSON.stringify({ theme_preference: nextTheme }),
            });
        } catch (error) {
            console.warn('Theme preference could not be persisted.', error);
        }
    }
};

window.toggleTheme = () => {
    const order = ['light', 'dark', 'system'];
    const current = document.documentElement.dataset.theme || window.__theme?.initial || 'system';
    const currentIndex = order.indexOf(current);
    const nextTheme = order[(currentIndex + 1) % order.length];

    return window.applyTheme(nextTheme);
};

window.initializeTheme = () => {
    const settings = window.__theme || {};
    const serverTheme = settings.initial || 'system';
    const localTheme = localStorage.getItem(STORAGE_KEY);
    const theme = settings.enabled === false
        ? 'light'
        : (settings.persistUrl ? serverTheme : (localTheme || serverTheme));

    window.applyTheme(theme, { persist: false });

    const media = window.matchMedia('(prefers-color-scheme: dark)');
    media.addEventListener?.('change', () => {
        if ((document.documentElement.dataset.theme || 'system') === 'system') {
            window.applyTheme('system', { persist: false });
        }
    });
};

window.Alpine = Alpine;

window.initializeTheme();
Alpine.start();
