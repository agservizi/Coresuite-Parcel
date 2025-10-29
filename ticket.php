<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/ui.php';
require_once __DIR__ . '/modules/tickets.php';

if (!is_user_authenticated()) {
    header('Location: login.php');
    exit;
}

ensure_session_freshness();

$role = current_user_role();
$allowedStatuses = ['Aperto', 'In attesa', 'Chiuso'];
$repository = new TicketRepository($pdo);

$feedback = null;
$errorMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
        $errorMessage = 'Sessione scaduta. Riprova.';
    } else {
        $ticketId = (int) ($_POST['ticket_id'] ?? 0);
        $newStatus = sanitize_input($_POST['status'] ?? '');

        if ($role !== 'Admin') {
            $errorMessage = 'Permessi insufficienti per modificare lo stato.';
        } elseif ($ticketId <= 0 || !in_array($newStatus, $allowedStatuses, true)) {
            $errorMessage = 'Richiesta non valida.';
        } else {
            if ($repository->updateStatus($ticketId, $newStatus)) {
                $feedback = 'Stato ticket aggiornato correttamente.';
            } else {
                $errorMessage = 'Impossibile aggiornare il ticket.';
            }
        }
    }
}

$search = sanitize_input($_GET['search'] ?? '');
$stateFilter = sanitize_input($_GET['stato'] ?? '');
if ($stateFilter !== '' && !in_array($stateFilter, $allowedStatuses, true)) {
    $stateFilter = '';
}

$tickets = $repository->all([
    'search' => $search !== '' ? $search : null,
    'stato' => $stateFilter !== '' ? $stateFilter : null,
    'utente_id' => $role === 'Cliente' ? current_user_id() : null,
]);

$csrfToken = generate_csrf_token(forceRefresh: true);
?>
<!DOCTYPE html>
<html lang="it" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket | Coresuite Parcel</title>
    <link rel="stylesheet" href="assets/css/build.css">
</head>
<body class="min-h-screen bg-slate-100 dark:bg-slate-950 text-slate-900 dark:text-slate-100">
    <div class="min-h-screen flex">
        <?php render_sidebar('ticket'); ?>
        <div class="flex-1 flex flex-col">
            <?php render_topbar('Ticket', 'Supporto e segnalazioni clienti'); ?>
            <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8 space-y-6">
                <?php if ($feedback) : ?>
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        <?php echo htmlspecialchars($feedback, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>
                <?php if ($errorMessage) : ?>
                    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
                        <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <section class="card">
                    <form method="GET" class="grid gap-4 md:grid-cols-3">
                        <div>
                            <label for="search" class="text-sm font-medium">Ricerca</label>
                            <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-yellow-200" placeholder="Cliente, spedizione o testo">
                        </div>
                        <div>
                            <label for="stato" class="text-sm font-medium">Stato</label>
                            <select id="stato" name="stato" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-yellow-200">
                                <option value="">Tutti</option>
                                <?php foreach ($allowedStatuses as $status) : ?>
                                    <option value="<?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $status === $stateFilter ? 'selected' : ''; ?>><?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="flex items-end gap-3">
                            <button type="submit" class="btn-primary">Filtra</button>
                            <a href="ticket.php" class="px-4 py-2 rounded-xl border border-slate-300 dark:border-slate-700 text-sm">Reset</a>
                        </div>
                    </form>
                </section>

                <section class="card">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold">Elenco ticket</h2>
                            <p class="text-sm text-slate-500"><?php echo count($tickets); ?> segnalazioni</p>
                        </div>
                        <a href="#" class="btn-primary">Nuovo ticket</a>
                    </div>
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="text-left text-xs uppercase text-slate-500">
                                <tr>
                                    <th class="pb-2 pr-4">Cliente</th>
                                    <th class="pb-2 pr-4">Spedizione</th>
                                    <th class="pb-2 pr-4">Messaggio</th>
                                    <th class="pb-2 pr-4">Stato</th>
                                    <th class="pb-2 pr-4">Creato</th>
                                    <?php if ($role === 'Admin') : ?>
                                        <th class="pb-2 pr-4">Azioni</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                <?php foreach ($tickets as $ticket) : ?>
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
                                        <td class="py-3 pr-4 font-medium"><?php echo htmlspecialchars($ticket['utente_nome'], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="py-3 pr-4 text-slate-500"><?php echo htmlspecialchars($ticket['spedizione_codice'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="py-3 pr-4 text-slate-500"><?php echo nl2br(htmlspecialchars($ticket['messaggio'], ENT_QUOTES, 'UTF-8')); ?></td>
                                        <td class="py-3 pr-4"><span class="badge"><?php echo htmlspecialchars($ticket['stato'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td class="py-3 pr-4 text-slate-500"><?php echo date('d/m/Y H:i', strtotime($ticket['created_at'])); ?></td>
                                        <?php if ($role === 'Admin') : ?>
                                            <td class="py-3 pr-4">
                                                <form method="POST" class="flex items-center gap-2">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                                                    <input type="hidden" name="ticket_id" value="<?php echo (int) $ticket['id']; ?>">
                                                    <select name="status" class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs">
                                                        <?php foreach ($allowedStatuses as $status) : ?>
                                                            <option value="<?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $status === $ticket['stato'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <button type="submit" class="px-3 py-1 rounded-lg bg-slate-900 text-white text-xs">Aggiorna</button>
                                                </form>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (!$tickets) : ?>
                                    <tr>
                                        <td colspan="<?php echo $role === 'Admin' ? 6 : 5; ?>" class="py-6 text-center text-slate-500">Nessun ticket aperto.</td>
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
