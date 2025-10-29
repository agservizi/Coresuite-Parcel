<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/ui.php';
require_once __DIR__ . '/modules/pickup.php';

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

$states = ['Richiesto', 'Accettato', 'Ritirato', 'In magazzino'];
$stateFilter = sanitize_input($_GET['stato'] ?? '');
if ($stateFilter !== '' && !in_array($stateFilter, $states, true)) {
    $stateFilter = '';
}

$fromDate = sanitize_input($_GET['from'] ?? '');
$toDate = sanitize_input($_GET['to'] ?? '');

$repository = new PickupRepository($pdo);
$pickups = $repository->upcoming([
    'stato' => $stateFilter !== '' ? $stateFilter : null,
    'from_date' => $fromDate !== '' ? $fromDate . ' 00:00:00' : null,
    'to_date' => $toDate !== '' ? $toDate . ' 23:59:59' : null,
]);

?>
<!DOCTYPE html>
<html lang="it" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ritiri | Coresuite Parcel</title>
    <link rel="stylesheet" href="assets/css/build.css">
</head>
<body class="min-h-screen bg-slate-100 dark:bg-slate-950 text-slate-900 dark:text-slate-100">
    <div class="min-h-screen flex">
        <?php render_sidebar('ritiri'); ?>
        <div class="flex-1 flex flex-col">
            <?php render_topbar('Ritiri', 'Programmazione e monitoraggio pickup'); ?>
            <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8 space-y-8">
                <section class="card">
                    <form method="GET" class="grid gap-4 md:grid-cols-4">
                        <div>
                            <label for="from" class="text-sm font-medium">Da</label>
                            <input type="date" id="from" name="from" value="<?php echo htmlspecialchars($fromDate, ENT_QUOTES, 'UTF-8'); ?>" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-yellow-200">
                        </div>
                        <div>
                            <label for="to" class="text-sm font-medium">A</label>
                            <input type="date" id="to" name="to" value="<?php echo htmlspecialchars($toDate, ENT_QUOTES, 'UTF-8'); ?>" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-yellow-200">
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
                            <a href="ritiri.php" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-sm">Reset</a>
                        </div>
                    </form>
                </section>

                <section class="card">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold">Calendario ritiri</h2>
                            <p class="text-sm text-slate-500"><?php echo count($pickups); ?> appuntamenti</p>
                        </div>
                        <a href="#" class="btn-primary">Nuovo ritiro</a>
                    </div>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="text-left text-xs uppercase text-slate-500">
                                <tr>
                                    <th class="pb-2 pr-4">Cliente</th>
                                    <th class="pb-2 pr-4">Indirizzo</th>
                                    <th class="pb-2 pr-4">Data/Fascia</th>
                                    <th class="pb-2 pr-4">Stato</th>
                                    <th class="pb-2 pr-4">Note</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                <?php foreach ($pickups as $pickup) :
                                    $address = json_decode($pickup['indirizzo'] ?? '', true) ?: [];
                                    ?>
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
                                        <td class="py-3 pr-4 font-medium"><?php echo htmlspecialchars($pickup['cliente_nome'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="py-3 pr-4 text-slate-500"><?php echo htmlspecialchars(($address['via'] ?? '') . ' ' . ($address['citta'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="py-3 pr-4 text-slate-500"><?php echo date('d/m/Y H:i', strtotime($pickup['data_ritiro'])); ?> · <?php echo htmlspecialchars($pickup['fascia_oraria'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="py-3 pr-4"><span class="badge"><?php echo htmlspecialchars($pickup['stato'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td class="py-3 pr-4 text-slate-500"><?php echo htmlspecialchars($pickup['note'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (!$pickups) : ?>
                                    <tr>
                                        <td colspan="5" class="py-6 text-center text-slate-500">Nessun ritiro programmato.</td>
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
