<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/ui.php';
require_once __DIR__ . '/modules/invoices.php';

if (!is_user_authenticated()) {
    header('Location: login.php');
    exit;
}

ensure_session_freshness();

$role = current_user_role();
$service = new InvoiceService($pdo);
$search = sanitize_input($_GET['search'] ?? '');

if ($role === 'Cliente') {
    $invoices = $service->allForClient((int) current_user_id());
    if ($search !== '') {
        $invoices = array_filter($invoices, static function (array $invoice) use ($search): bool {
            return str_contains(strtolower($invoice['spedizione_codice']), strtolower($search));
        });
    }
} else {
    $invoices = $service->all([
        'search' => $search !== '' ? $search : null,
    ]);
}

$totalAmount = array_reduce($invoices, static fn ($carry, $invoice) => $carry + (float) $invoice['totale'], 0.0);
$totalVat = array_reduce($invoices, static fn ($carry, $invoice) => $carry + (float) $invoice['iva'], 0.0);

?>
<!DOCTYPE html>
<html lang="it" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fatture | Coresuite Parcel</title>
    <link rel="stylesheet" href="assets/css/build.css">
</head>
<body class="min-h-screen bg-slate-100 dark:bg-slate-950 text-slate-900 dark:text-slate-100">
    <div class="min-h-screen flex">
        <?php render_sidebar('fatture'); ?>
        <div class="flex-1 flex flex-col">
            <?php render_topbar('Fatture', 'Archivio fiscale e rendicontazione'); ?>
            <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8 space-y-8">
                <section class="grid gap-4 md:grid-cols-3">
                    <div class="card">
                        <p class="text-sm text-slate-500">Fatture totali</p>
                        <p class="mt-2 text-3xl font-semibold"><?php echo count($invoices); ?></p>
                    </div>
                    <div class="card">
                        <p class="text-sm text-slate-500">Importo imponibile</p>
                        <p class="mt-2 text-3xl font-semibold"><?php echo format_currency((float) $totalAmount); ?></p>
                    </div>
                    <div class="card">
                        <p class="text-sm text-slate-500">IVA</p>
                        <p class="mt-2 text-3xl font-semibold"><?php echo format_currency((float) $totalVat); ?></p>
                    </div>
                </section>

                <section class="card">
                    <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                        <div class="sm:flex-1">
                            <label for="search" class="text-sm font-medium">Ricerca</label>
                            <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-yellow-200" placeholder="Codice spedizione o cliente">
                        </div>
                        <div class="flex gap-3">
                            <button type="submit" class="btn-primary">Filtra</button>
                            <a href="fatture.php" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-sm">Reset</a>
                        </div>
                    </form>
                </section>

                <section class="card">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold">Archivio fatture</h2>
                            <p class="text-sm text-slate-500"><?php echo count($invoices); ?> documenti</p>
                        </div>
                        <?php if ($role === 'Admin') : ?>
                            <a href="#" class="btn-primary">Genera nuova fattura</a>
                        <?php endif; ?>
                    </div>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="text-left text-xs uppercase text-slate-500">
                                <tr>
                                    <th class="pb-2 pr-4">Spedizione</th>
                                    <?php if ($role !== 'Cliente') : ?>
                                        <th class="pb-2 pr-4">Cliente</th>
                                    <?php endif; ?>
                                    <th class="pb-2 pr-4">Totale</th>
                                    <th class="pb-2 pr-4">IVA</th>
                                    <th class="pb-2 pr-4">Data</th>
                                    <th class="pb-2 pr-4">PDF</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                <?php foreach ($invoices as $invoice) : ?>
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
                                        <td class="py-3 pr-4 font-medium"><?php echo htmlspecialchars($invoice['spedizione_codice'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <?php if ($role !== 'Cliente') : ?>
                                            <td class="py-3 pr-4 text-slate-500"><?php echo htmlspecialchars($invoice['cliente_nome'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <?php endif; ?>
                                        <td class="py-3 pr-4"><?php echo format_currency((float) $invoice['totale']); ?></td>
                                        <td class="py-3 pr-4 text-slate-500"><?php echo format_currency((float) $invoice['iva']); ?></td>
                                        <td class="py-3 pr-4 text-slate-500"><?php echo date('d/m/Y', strtotime($invoice['created_at'])); ?></td>
                                        <td class="py-3 pr-4">
                                            <a href="<?php echo htmlspecialchars($invoice['pdf_path'], ENT_QUOTES, 'UTF-8'); ?>" class="text-[var(--coresuite-primary)]" target="_blank" rel="noopener noreferrer">Scarica</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (!$invoices) : ?>
                                    <tr>
                                        <td colspan="<?php echo $role !== 'Cliente' ? 6 : 5; ?>" class="py-6 text-center text-slate-500">Nessuna fattura disponibile.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>
        </div>
    </div>
    <script src="assets/js/app.js" defer></script>
</body>
</html>
