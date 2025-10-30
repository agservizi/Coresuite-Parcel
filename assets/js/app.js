document.addEventListener('DOMContentLoaded', () => {
    const root = document.documentElement;
    const body = document.body;
    const themeToggle = document.querySelector('[data-theme-toggle]');
    const themeIcons = {
        light: document.querySelectorAll('[data-theme-icon="light"]'),
        dark: document.querySelectorAll('[data-theme-icon="dark"]'),
    };
    const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
    const sidebar = document.querySelector('[data-sidebar]');
    const desktopMedia = window.matchMedia('(min-width: 768px)');
    let sidebarResizeTimer = null;

    const refreshThemeIcon = () => {
        const isDark = root.classList.contains('dark');
        themeIcons.light.forEach((icon) => icon.classList.toggle('hidden', isDark));
        themeIcons.dark.forEach((icon) => icon.classList.toggle('hidden', !isDark));
    };

    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const isDark = root.classList.toggle('dark');
            localStorage.setItem('coresuite-theme', isDark ? 'dark' : 'light');
            refreshThemeIcon();
        });
    }

    const savedTheme = localStorage.getItem('coresuite-theme');
    if (savedTheme === 'dark') {
        root.classList.add('dark');
    } else if (savedTheme === 'light') {
        root.classList.remove('dark');
    }
    refreshThemeIcon();

    const updateSidebarState = () => {
        if (!sidebarToggle || !sidebar) {
            return;
        }

        const isDesktop = desktopMedia.matches;
        const collapsed = body.classList.contains('sidebar-collapsed');
        const isVisible = isDesktop && !collapsed;

    sidebarToggle.setAttribute('aria-expanded', isVisible ? 'true' : 'false');
    sidebarToggle.setAttribute('aria-pressed', collapsed ? 'true' : 'false');
    sidebar.setAttribute('aria-hidden', isVisible ? 'false' : 'true');
    };

    const measureSidebar = () => {
        if (!sidebar || !desktopMedia.matches) {
            body.style.removeProperty('--sidebar-width');
            return;
        }

        const wasCollapsed = body.classList.contains('sidebar-collapsed');
        if (wasCollapsed) {
            body.classList.remove('sidebar-collapsed');
        }

        const width = sidebar.getBoundingClientRect().width;

        if (wasCollapsed) {
            body.classList.add('sidebar-collapsed');
        }

        if (width) {
            body.style.setProperty('--sidebar-width', `${width}px`);
        }
    };

    if (sidebarToggle && sidebar) {
        const storedSidebar = localStorage.getItem('coresuite-sidebar');
        if (storedSidebar === 'collapsed') {
            body.classList.add('sidebar-collapsed');
        }

        measureSidebar();
        updateSidebarState();

        sidebarToggle.addEventListener('click', () => {
            if (!desktopMedia.matches) {
                return;
            }

            const collapsed = body.classList.toggle('sidebar-collapsed');
            localStorage.setItem('coresuite-sidebar', collapsed ? 'collapsed' : 'expanded');
            updateSidebarState();

            if (!collapsed) {
                measureSidebar();
            }
        });

        const handleDesktopChange = () => {
            measureSidebar();
            updateSidebarState();
        };

        if (typeof desktopMedia.addEventListener === 'function') {
            desktopMedia.addEventListener('change', handleDesktopChange);
        } else if (typeof desktopMedia.addListener === 'function') {
            desktopMedia.addListener(handleDesktopChange);
        }

        window.addEventListener('resize', () => {
            clearTimeout(sidebarResizeTimer);
            sidebarResizeTimer = window.setTimeout(() => {
                measureSidebar();
                updateSidebarState();
            }, 150);
        });
    }
});
