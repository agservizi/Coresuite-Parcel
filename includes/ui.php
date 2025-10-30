<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

function coresuite_navigation_items(): array
{
    return [
        'dashboard' => ['label' => 'Dashboard', 'href' => 'dashboard.php'],
        'spedizioni' => ['label' => 'Spedizioni', 'href' => 'spedizioni.php'],
        'ritiri' => ['label' => 'Ritiri', 'href' => 'ritiri.php'],
        'ticket' => ['label' => 'Ticket', 'href' => 'ticket.php'],
        'fatture' => ['label' => 'Fatture', 'href' => 'fatture.php'],
        'impostazioni' => ['label' => 'Impostazioni', 'href' => 'impostazioni.php'],
    ];
}

function render_sidebar(string $activeKey): void
{
    $role = current_user_role();
    $items = coresuite_navigation_items();

    if ($role === 'Cliente') {
        $allowed = ['dashboard', 'ticket', 'fatture'];
        $items = array_intersect_key($items, array_flip($allowed));
    } elseif ($role === 'Driver') {
        $allowed = ['dashboard', 'spedizioni', 'ritiri'];
        $items = array_intersect_key($items, array_flip($allowed));
    }
    ?>
    <aside id="app-sidebar" data-sidebar class="hidden md:flex md:w-64 lg:w-72 flex-col bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800" aria-label="Navigazione" aria-hidden="false">
        <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-full bg-[var(--coresuite-primary)] flex items-center justify-center font-bold text-slate-900">CP</div>
                <div>
                    <p class="text-lg font-semibold">Coresuite Parcel</p>
                    <p class="text-xs text-slate-500"><?php echo htmlspecialchars((string) $role, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </div>
        </div>
        <nav class="flex-1 px-3 py-4 space-y-1 text-sm">
            <?php foreach ($items as $key => $item) :
                $isActive = $key === $activeKey;
                $classes = $isActive ? 'bg-slate-100 dark:bg-slate-800 font-semibold' : 'hover:bg-slate-100 dark:hover:bg-slate-800';
                ?>
                <a href="<?php echo htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8'); ?>"
                   class="flex items-center gap-3 rounded-xl px-3 py-2 <?php echo $classes; ?>">
                    <?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 text-sm">
            <a href="logout.php" class="text-slate-500 hover:text-slate-700">Logout</a>
        </div>
    </aside>
    <?php
}

function render_topbar(string $title, string $subtitle = ''): void
{
    ?>
    <header class="sticky top-0 z-10 bg-white/80 dark:bg-slate-900/80 backdrop-blur border-b border-slate-200 dark:border-slate-800">
        <div class="px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button type="button" data-sidebar-toggle class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition-colors duration-150 hover:text-slate-900 focus:outline-none focus:ring focus:ring-slate-200 dark:border-slate-700 dark:text-slate-300 dark:hover:text-white dark:focus:ring-slate-700" aria-controls="app-sidebar" aria-expanded="true">
                    <span class="sr-only">Apri o chiudi la sidebar</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <div>
                    <h1 class="text-2xl font-semibold"><?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></h1>
                    <?php if ($subtitle !== '') : ?>
                        <p class="text-sm text-slate-500"><?php echo htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <button type="button" data-theme-toggle class="btn-primary">Tema</button>
        </div>
    </header>
    <?php
}
