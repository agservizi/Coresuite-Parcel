<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/ui.php';
require_once __DIR__ . '/modules/shipments.php';

if (!is_user_authenticated()) {
    header('Location: login.php');
    exit;
}

ensure_session_freshness();

$role = current_user_role();
if ($role !== 'Admin' && $role !== 'Driver') {
    http_response_code(403);
    exit('Accesso negato.');
}

$states = ['In attesa', 'In transito', 'Consegnata', 'Annullata'];
$search = sanitize_input($_GET['search'] ?? '');
$stateFilter = sanitize_input($_GET['stato'] ?? '');
if ($stateFilter !== '' && !in_array($stateFilter, $states, true)) {
    $stateFilter = '';
}

$repository = new ShipmentRepository($pdo);
$shipments = $repository->all([
    'search' => $search !== '' ? $search : null,
    'stato' => $stateFilter !== '' ? $stateFilter : null,
]);

$countByStatus = array_fill_keys($states, 0);
foreach ($shipments as $shipment) {
    if (isset($countByStatus[$shipment['stato']])) {
        $countByStatus[$shipment['stato']]++;
    }
}
?>
<!DOCTYPE html>
<html lang="it" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spedizioni | Coresuite Parcel</title>
    <link rel="stylesheet" href="assets/css/build.css">
</head>
<body class="min-h-screen bg-slate-100 dark:bg-slate-950 text-slate-900 dark:text-slate-100">
    <div class="min-h-screen flex">
        <?php render_sidebar('spedizioni'); ?>
        <div class="flex-1 flex flex-col">
            <?php render_topbar('Spedizioni', 'Gestione spedizioni e tracking'); ?>
            <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8 space-y-8">
                <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <?php foreach ($states as $state) : ?>
                        <div class="card">
                            <p class="text-sm text-slate-500"><?php echo htmlspecialchars($state, ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="mt-2 text-3xl font-semibold"><?php echo $countByStatus[$state]; ?></p>
                        </div>
                    <?php endforeach; ?>
                </section>

                <section class="card">
                    <form method="GET" class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label for="search" class="text-sm font-medium">Ricerca</label>
                            <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-yellow-200" placeholder="Codice, mittente, destinatario">
                        </div>
                        <div>
                            <label for="stato" class="text-sm font-medium">Stato</label>
                            <select id="stato" name="stato" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-yellow-200">
                                <option value="">Tutti</option>
                                <?php foreach ($states as $state) : ?>
                                    <option value="<?php echo htmlspecialchars($state, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $state === $stateFilter ? 'selected' : ''; ?>><?php echo htmlspecialchars($state, ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="flex items-end gap-3">
                            <button type="submit" class="btn-primary">Filtra</button>
                            <a href="spedizioni.php" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-sm">Reset</a>
                        </div>
                    </form>
                </section>

                <section class="card">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold">Elenco spedizioni</h2>
                            <p class="text-sm text-slate-500"><?php echo count($shipments); ?> risultati</p>
                        </div>
                        <a href="#" class="btn-primary">Nuova spedizione</a>
                    </div>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="text-left text-xs uppercase text-slate-500">
                                <tr>
                                    <th class="pb-2 pr-4">Codice</th>
                                    <th class="pb-2 pr-4">Cliente</th>
                                    <th class="pb-2 pr-4">Mittente</th>
                                    <th class="pb-2 pr-4">Destinatario</th>
                                    <th class="pb-2 pr-4">Stato</th>
                                    <th class="pb-2 pr-4">Creato</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                <?php foreach ($shipments as $shipment) :
                                    $mittente = json_decode($shipment['mittente'] ?? '', true) ?: [];
                                    $destinatario = json_decode($shipment['destinatario'] ?? '', true) ?: [];
                                    ?>
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
                                        <td class="py-3 pr-4 font-medium"><?php echo htmlspecialchars($shipment['codice'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="py-3 pr-4"><?php echo htmlspecialchars($shipment['cliente_nome'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="py-3 pr-4 text-slate-500"><?php echo htmlspecialchars(($mittente['nome'] ?? '') . ' ' . ($mittente['citta'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="py-3 pr-4 text-slate-500"><?php echo htmlspecialchars(($destinatario['nome'] ?? '') . ' ' . ($destinatario['citta'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="py-3 pr-4"><span class="badge"><?php echo htmlspecialchars($shipment['stato'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td class="py-3 pr-4 text-slate-500"><?php echo date('d/m/Y', strtotime($shipment['created_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (!$shipments) : ?>
                                    <tr>
                                        <td colspan="6" class="py-6 text-center text-slate-500">Nessuna spedizione trovata.</td>
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
