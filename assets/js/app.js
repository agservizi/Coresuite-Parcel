document.addEventListener('DOMContentLoaded', () => {
    const themeToggle = document.querySelector('[data-theme-toggle]');

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
});
