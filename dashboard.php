<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/modules/shipments.php';
require_once __DIR__ . '/modules/pickup.php';
require_once __DIR__ . '/modules/invoices.php';

if (!is_user_authenticated()) {
    header('Location: login.php');
    exit;
}

ensure_session_freshness();

$role = current_user_role();
$shipmentRepo = new ShipmentRepository($pdo);
$pickupRepo = new PickupRepository($pdo);
$invoiceService = new InvoiceService($pdo);

$shipments = $shipmentRepo->all(['stato' => $_GET['stato'] ?? null]);
$pickups = $pickupRepo->upcoming();
$invoices = [];

if ($role === 'Cliente' && current_user_id()) {
    $invoices = $invoiceService->allForClient(current_user_id());
}
?>
<!DOCTYPE html>
<html lang="it" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Coresuite Parcel</title>
    <link rel="stylesheet" href="assets/css/build.css">
</head>
<body class="min-h-screen bg-slate-100 dark:bg-slate-950 text-slate-900 dark:text-slate-100">
    <div class="min-h-screen flex">
        <aside class="hidden md:flex md:w-64 lg:w-72 flex-col bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800">
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
                <a href="#" class="flex items-center gap-3 rounded-xl px-3 py-2 bg-slate-100 dark:bg-slate-800">Dashboard</a>
                <a href="#" class="flex items-center gap-3 rounded-xl px-3 py-2 hover:bg-slate-100 dark:hover:bg-slate-800">Spedizioni</a>
                <a href="#" class="flex items-center gap-3 rounded-xl px-3 py-2 hover:bg-slate-100 dark:hover:bg-slate-800">Ritiri</a>
                <a href="#" class="flex items-center gap-3 rounded-xl px-3 py-2 hover:bg-slate-100 dark:hover:bg-slate-800">Ticket</a>
                <a href="#" class="flex items-center gap-3 rounded-xl px-3 py-2 hover:bg-slate-100 dark:hover:bg-slate-800">Fatture</a>
                <a href="#" class="flex items-center gap-3 rounded-xl px-3 py-2 hover:bg-slate-100 dark:hover:bg-slate-800">Impostazioni</a>
            </nav>
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 text-sm">
                <a href="logout.php" class="text-slate-500 hover:text-slate-700">Logout</a>
            </div>
        </aside>

        <div class="flex-1 flex flex-col">
            <header class="sticky top-0 z-10 bg-white/80 dark:bg-slate-900/80 backdrop-blur border-b border-slate-200 dark:border-slate-800">
                <div class="px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold">Dashboard</h1>
                        <p class="text-sm text-slate-500">Panoramica operativa</p>
                    </div>
                    <button type="button" data-theme-toggle class="btn-primary">Tema</button>
                </div>
            </header>

            <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8 space-y-8">
                <section>
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <div class="card">
                            <p class="text-sm text-slate-500">Spedizioni attive</p>
                            <p class="mt-2 text-3xl font-semibold"><?php echo count($shipments); ?></p>
                        </div>
                        <div class="card">
                            <p class="text-sm text-slate-500">Ritiri programmati</p>
                            <p class="mt-2 text-3xl font-semibold"><?php echo count($pickups); ?></p>
                        </div>
                        <div class="card">
                            <p class="text-sm text-slate-500">Ticket aperti</p>
                            <p class="mt-2 text-3xl font-semibold">0</p>
                        </div>
                        <div class="card">
                            <p class="text-sm text-slate-500">Fatturato mese</p>
                            <p class="mt-2 text-3xl font-semibold"><?php echo format_currency(0); ?></p>
                        </div>
                    </div>
                </section>

                <section class="grid gap-6 lg:grid-cols-3">
                    <div class="card lg:col-span-2">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-semibold">Ultime spedizioni</h2>
                                <p class="text-sm text-slate-500">Monitoraggio stato in tempo reale</p>
                            </div>
                            <a href="#" class="text-sm text-slate-500 hover:text-slate-700">Vedi tutte</a>
                        </div>
                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="text-left text-xs uppercase text-slate-500">
                                    <tr>
                                        <th class="pb-2 pr-4">Codice</th>
                                        <th class="pb-2 pr-4">Cliente</th>
                                        <th class="pb-2 pr-4">Stato</th>
                                        <th class="pb-2 pr-4">Creato</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                    <?php foreach ($shipments as $shipment) : ?>
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
                                            <td class="py-3 pr-4 font-medium"><?php echo htmlspecialchars($shipment['codice'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="py-3 pr-4"><?php echo htmlspecialchars($shipment['cliente_nome'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="py-3 pr-4">
                                                <span class="badge"><?php echo htmlspecialchars($shipment['stato'], ENT_QUOTES, 'UTF-8'); ?></span>
                                            </td>
                                            <td class="py-3 pr-4 text-slate-500"><?php echo date('d/m/Y', strtotime($shipment['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (!$shipments) : ?>
                                        <tr>
                                            <td colspan="4" class="py-6 text-center text-slate-500">Nessuna spedizione registrata.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card space-y-4">
                        <div>
                            <h2 class="text-lg font-semibold">Ritiri imminenti</h2>
                            <p class="text-sm text-slate-500">Driver e fasce orarie</p>
                        </div>
                        <ul class="space-y-3 text-sm">
                            <?php foreach ($pickups as $pickup) : ?>
                                <li class="rounded-xl border border-slate-200 dark:border-slate-800 px-3 py-2">
                                    <p class="font-medium"><?php echo htmlspecialchars($pickup['cliente_nome'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="text-slate-500 text-xs"><?php echo date('d/m/Y H:i', strtotime($pickup['data_ritiro'])); ?> · <?php echo htmlspecialchars($pickup['fascia_oraria'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="text-xs text-slate-500">Stato: <?php echo htmlspecialchars($pickup['stato'], ENT_QUOTES, 'UTF-8'); ?></p>
                                </li>
                            <?php endforeach; ?>
                            <?php if (!$pickups) : ?>
                                <li class="text-slate-500">Nessun ritiro programmato.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </section>

                <?php if ($role === 'Cliente') : ?>
                    <section class="card">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-semibold">Fatture recenti</h2>
                                <p class="text-sm text-slate-500">Scarica le tue fatture in PDF</p>
                            </div>
                            <a href="#" class="text-sm text-slate-500 hover:text-slate-700">Archivio</a>
                        </div>
                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="text-left text-xs uppercase text-slate-500">
                                    <tr>
                                        <th class="pb-2 pr-4">Spedizione</th>
                                        <th class="pb-2 pr-4">Totale</th>
                                        <th class="pb-2 pr-4">IVA</th>
                                        <th class="pb-2 pr-4">PDF</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                    <?php foreach ($invoices as $invoice) : ?>
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
                                            <td class="py-3 pr-4 font-medium"><?php echo htmlspecialchars($invoice['spedizione_codice'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td class="py-3 pr-4"><?php echo format_currency((float) $invoice['totale']); ?></td>
                                            <td class="py-3 pr-4 text-slate-500"><?php echo format_currency((float) $invoice['iva']); ?></td>
                                            <td class="py-3 pr-4">
                                                <a href="<?php echo htmlspecialchars($invoice['pdf_path'], ENT_QUOTES, 'UTF-8'); ?>" class="text-[var(--coresuite-primary)]" target="_blank" rel="noopener noreferrer">Apri</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (!$invoices) : ?>
                                        <tr>
                                            <td colspan="4" class="py-6 text-center text-slate-500">Nessuna fattura disponibile.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="assets/js/app.js" defer></script>
</body>
</html>
