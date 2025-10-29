<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$code = sanitize_input($_GET['code'] ?? '');
$shipment = null;
$events = [];

if ($code) {
    $stmt = $pdo->prepare('SELECT s.*, u.nome AS cliente_nome FROM spedizioni s JOIN users u ON u.id = s.id_cliente WHERE s.codice = :code');
    $stmt->execute([':code' => $code]);
    $shipment = $stmt->fetch();

    if ($shipment) {
        $timelineStmt = $pdo->prepare('SELECT * FROM tracking WHERE id_spedizione = :id ORDER BY timestamp ASC');
        $timelineStmt->execute([':id' => $shipment['id']]);
        $events = $timelineStmt->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="it" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking | Coresuite Parcel</title>
    <link rel="stylesheet" href="assets/css/build.css">
</head>
<body class="min-h-screen bg-slate-100 dark:bg-slate-950 text-slate-900 dark:text-slate-100">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="mb-8 text-center">
            <div class="mx-auto h-12 w-12 rounded-full bg-[var(--coresuite-primary)] flex items-center justify-center font-bold text-slate-900">CP</div>
            <h1 class="mt-4 text-3xl font-semibold">Tracking spedizione</h1>
            <p class="text-sm text-slate-500">Inserisci il codice spedizione o scansiona il QR Code.</p>
        </div>

        <form action="tracking.php" method="GET" class="mb-10 grid gap-3 sm:grid-cols-[1fr_auto]">
            <input type="text" name="code" value="<?php echo htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Codice spedizione" class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:outline-none focus:ring focus:ring-yellow-200" required>
            <button type="submit" class="btn-primary">Traccia</button>
        </form>

        <?php if ($shipment) : ?>
            <div class="card">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <p class="text-sm text-slate-500">Codice spedizione</p>
                        <p class="text-xl font-semibold"><?php echo htmlspecialchars($shipment['codice'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="text-sm text-slate-500">Cliente: <?php echo htmlspecialchars($shipment['cliente_nome'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-slate-500">Stato attuale</p>
                        <span class="badge inline-flex justify-end"><?php echo htmlspecialchars($shipment['stato'], ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                </div>
                <div class="mt-6">
                    <h2 class="text-lg font-semibold">Timeline spedizione</h2>
                    <ol class="mt-4 space-y-4 border-l border-slate-200 dark:border-slate-800 pl-4">
                        <?php foreach ($events as $event) : ?>
                            <li class="relative">
                                <span class="absolute -left-[11px] top-1.5 h-5 w-5 rounded-full border-2 border-white bg-[var(--coresuite-primary)]"></span>
                                <div class="rounded-xl border border-slate-200 dark:border-slate-800 px-4 py-3">
                                    <p class="font-medium"><?php echo htmlspecialchars($event['stato'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <p class="text-sm text-slate-500"><?php echo date('d/m/Y H:i', strtotime($event['timestamp'])); ?></p>
                                    <?php if (!empty($event['note'])) : ?>
                                        <p class="text-sm text-slate-500 mt-1"><?php echo htmlspecialchars($event['note'], ENT_QUOTES, 'UTF-8'); ?></p>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                        <?php if (!$events) : ?>
                            <li class="text-sm text-slate-500">Nessun evento registrato.</li>
                        <?php endif; ?>
                    </ol>
                </div>
            </div>
        <?php elseif ($code) : ?>
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-600">
                Nessuna spedizione trovata per il codice inserito.
            </div>
        <?php endif; ?>
    </div>
    <script src="assets/js/app.js" defer></script>
</body>
</html>
