document.addEventListener('DOMContentLoaded', () => {
    const themeToggle = document.querySelector('[data-theme-toggle]');
    const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
    const sidebar = document.querySelector('[data-sidebar]');
    const body = document.body;

    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const root = document.documentElement;
            const isDark = root.classList.toggle('dark');
            localStorage.setItem('coresuite-theme', isDark ? 'dark' : 'light');
        });
    }

    const savedTheme = localStorage.getItem('coresuite-theme');
    if (savedTheme === 'dark') {
        document.documentElement.classList.add('dark');
    }

    const desktopMedia = window.matchMedia('(min-width: 768px)');

    const updateSidebarState = () => {
        if (!sidebarToggle || !sidebar) {
            return;
        }

        const isDesktop = desktopMedia.matches;
        const isCollapsed = body.classList.contains('sidebar-collapsed') && isDesktop;
        const isVisible = isDesktop && !isCollapsed;

        sidebarToggle.setAttribute('aria-expanded', isVisible ? 'true' : 'false');
        sidebar.setAttribute('aria-hidden', isVisible ? 'false' : 'true');
    };

    if (sidebarToggle && sidebar) {
        const storedSidebar = localStorage.getItem('coresuite-sidebar');
        if (storedSidebar === 'collapsed') {
            body.classList.add('sidebar-collapsed');
        }

        updateSidebarState();

        sidebarToggle.addEventListener('click', () => {
            const collapsed = body.classList.toggle('sidebar-collapsed');
            localStorage.setItem('coresuite-sidebar', collapsed ? 'collapsed' : 'expanded');
            updateSidebarState();
        });

        if (typeof desktopMedia.addEventListener === 'function') {
            desktopMedia.addEventListener('change', updateSidebarState);
        } else if (typeof desktopMedia.addListener === 'function') {
            desktopMedia.addListener(updateSidebarState);
        }
    }
});
