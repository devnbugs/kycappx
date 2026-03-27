import './bootstrap';

const STORAGE_KEY = 'kycappx.theme';
const DASHBOARD_SIDEBAR_STORAGE_PREFIX = 'kycappx.dashboard.sidebar';

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

window.createDashboardShell = (workspace = 'default') => ({
    mobileNav: false,
    desktopSidebarVisible: true,
    desktopMedia: null,

    init() {
        this.desktopMedia = window.matchMedia('(min-width: 1024px)');

        const storedState = window.localStorage.getItem(`${DASHBOARD_SIDEBAR_STORAGE_PREFIX}.${workspace}`);

        if (storedState !== null) {
            this.desktopSidebarVisible = storedState === 'true';
        }

        const syncViewport = (event) => {
            this.mobileNav = false;
        };

        syncViewport(this.desktopMedia);

        if (typeof this.desktopMedia.addEventListener === 'function') {
            this.desktopMedia.addEventListener('change', syncViewport);
        } else {
            this.desktopMedia.addListener(syncViewport);
        }
    },

    isDesktop() {
        return this.desktopMedia?.matches ?? window.matchMedia('(min-width: 1024px)').matches;
    },

    toggleSidebar() {
        if (this.isDesktop()) {
            this.desktopSidebarVisible = !this.desktopSidebarVisible;
            window.localStorage.setItem(
                `${DASHBOARD_SIDEBAR_STORAGE_PREFIX}.${workspace}`,
                String(this.desktopSidebarVisible),
            );

            return;
        }

        this.mobileNav = !this.mobileNav;
    },

    openMobileNav() {
        this.mobileNav = true;
    },

    closeMobileNav() {
        this.mobileNav = false;
    },
});

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

window.initializeTheme();
