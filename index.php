<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
?>
<!DOCTYPE html>
<html lang="it" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coresuite Parcel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Nunito+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/build.css">
</head>
<body class="h-full bg-slate-100 dark:bg-slate-950 text-slate-900 dark:text-slate-100">
    <div class="min-h-screen flex flex-col">
        <header class="border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-full bg-[var(--coresuite-primary)] flex items-center justify-center font-bold text-slate-900">CP</div>
                    <div>
                        <p class="text-lg font-semibold">Coresuite Parcel</p>
                        <p class="text-sm text-slate-500">Gestione spedizioni B2B/B2C</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" data-theme-toggle class="btn-primary">Toggle Theme</button>
                    <?php if (is_user_authenticated()) : ?>
                        <a href="dashboard.php" class="px-4 py-2 rounded-xl bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900">Vai alla dashboard</a>
                    <?php else : ?>
                        <a href="login.php" class="px-4 py-2 rounded-xl bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900">Accedi</a>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <main class="flex-1">
            <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16 grid gap-10 lg:grid-cols-2 items-center">
                <div class="space-y-6">
                    <span class="badge">Suite Gestionale</span>
                    <h1 class="text-4xl sm:text-5xl font-semibold leading-tight">Coordina spedizioni, ritiri e fatturazione in un unico hub.</h1>
                    <p class="text-lg text-slate-600 dark:text-slate-300">Coresuite Parcel centralizza operazioni logistiche B2B/B2C con dashboard multi-ruolo, tracking in tempo reale e notifiche intelligenti.</p>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="login.php" class="btn-primary">Accedi all'area riservata</a>
                        <a href="tracking.php" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700">Traccia una spedizione</a>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="card">
                        <h3 class="text-lg font-semibold">Tracking pubblico</h3>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Timeline dinamica, QR code e stati aggiornati per i destinatari.</p>
                    </div>
                    <div class="card">
                        <h3 class="text-lg font-semibold">Pickup intelligente</h3>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Calendario driver, stato iterativo e notifiche automatiche.</p>
                    </div>
                    <div class="card">
                        <h3 class="text-lg font-semibold">Fatture smart</h3>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">PDF A6, calcolo IVA e archivio filtrabile a prova di audit.</p>
                    </div>
                    <div class="card">
                        <h3 class="text-lg font-semibold">Multi-ruolo</h3>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Admin, clienti e driver con esperienze dedicate e sicure.</p>
                    </div>
                </div>
            </section>
        </main>

        <footer class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-sm text-slate-500">&copy; <?php echo date('Y'); ?> Coresuite. Tutti i diritti riservati.</p>
                <div class="flex gap-3 text-sm text-slate-500">
                    <a href="#">Condizioni d'uso</a>
                    <a href="#">Privacy</a>
                </div>
            </div>
        </footer>
    </div>

    <script src="assets/js/app.js" defer></script>
</body>
</html>
