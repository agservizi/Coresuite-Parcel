<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/ui.php';
require_once __DIR__ . '/modules/settings.php';

if (!is_user_authenticated()) {
    header('Location: login.php');
    exit;
}

ensure_session_freshness();

$role = current_user_role();
if ($role !== 'Admin') {
    http_response_code(403);
    exit('Accesso negato.');
}

$repository = new SettingsRepository($pdo);
$feedback = null;
$errorMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
        $errorMessage = 'Sessione scaduta. Riprova.';
    } else {
        $updated = 0;
        $submitted = $_POST['settings'] ?? [];

        if (is_array($submitted)) {
            foreach ($submitted as $key => $value) {
                $key = trim((string) $key);
                if ($key === '') {
                    continue;
                }
                $value = trim((string) $value);
                if ($repository->upsert($key, $value === '' ? null : $value)) {
                    $updated++;
                }
            }
        }

        $newKey = trim((string) ($_POST['new_key'] ?? ''));
        $newValue = trim((string) ($_POST['new_value'] ?? ''));
        if ($newKey !== '') {
            if (!preg_match('/^[A-Z0-9_]+$/i', $newKey)) {
                $errorMessage = 'La chiave può contenere solo lettere, numeri e underscore.';
            } elseif ($repository->upsert($newKey, $newValue === '' ? null : $newValue)) {
                $updated++;
            }
        }

        if (!$errorMessage) {
            $feedback = $updated > 0 ? 'Impostazioni aggiornate.' : 'Nessuna modifica rilevata.';
        }
    }
}

$settings = $repository->all();
$csrfToken = generate_csrf_token(forceRefresh: true);
?>
<!DOCTYPE html>
<html lang="it" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impostazioni | Coresuite Parcel</title>
    <link rel="stylesheet" href="assets/css/build.css">
</head>
<body class="min-h-screen bg-slate-100 dark:bg-slate-950 text-slate-900 dark:text-slate-100">
    <div class="min-h-screen flex">
        <?php render_sidebar('impostazioni'); ?>
        <div class="flex-1 flex flex-col">
            <?php render_topbar('Impostazioni', 'Configurazioni globali della piattaforma'); ?>
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

                <form method="POST" class="card space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold">Parametri applicativi</h2>
                            <p class="text-sm text-slate-500">Modifica le impostazioni chiave della suite</p>
                        </div>
                        <button type="submit" class="btn-primary">Salva modifiche</button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="text-left text-xs uppercase text-slate-500">
                                <tr>
                                    <th class="pb-2 pr-4">Chiave</th>
                                    <th class="pb-2 pr-4">Valore</th>
                                    <th class="pb-2 pr-4">Ultimo aggiornamento</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                <?php foreach ($settings as $setting) : ?>
                                    <tr>
                                        <td class="py-3 pr-4 font-medium align-top">
                                            <input type="text" value="<?php echo htmlspecialchars($setting['chiave'], ENT_QUOTES, 'UTF-8'); ?>" disabled class="w-full rounded-lg border border-transparent bg-transparent font-medium">
                                        </td>
                                        <td class="py-3 pr-4 align-top">
                                            <textarea name="settings[<?php echo htmlspecialchars($setting['chiave'], ENT_QUOTES, 'UTF-8'); ?>]" rows="2" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-yellow-200"><?php echo htmlspecialchars((string) $setting['valore'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                                        </td>
                                        <td class="py-3 pr-4 text-slate-500 align-top"><?php echo $setting['updated_at'] ? date('d/m/Y H:i', strtotime($setting['updated_at'])) : '-'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (!$settings) : ?>
                                    <tr>
                                        <td colspan="3" class="py-6 text-center text-slate-500">Nessuna impostazione registrata.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="rounded-xl border border-dashed border-slate-300 dark:border-slate-700 p-4">
                        <h3 class="text-sm font-semibold">Nuova impostazione</h3>
                        <p class="text-xs text-slate-500">Opzionale: aggiungi una chiave personalizzata.</p>
                        <div class="mt-3 grid gap-3 md:grid-cols-2">
                            <div>
                                <label for="new_key" class="text-xs font-medium uppercase tracking-wide">Chiave</label>
                                <input type="text" id="new_key" name="new_key" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-yellow-200" placeholder="ES: SLA_NOTIFICA_MINUTI">
                            </div>
                            <div>
                                <label for="new_value" class="text-xs font-medium uppercase tracking-wide">Valore</label>
                                <input type="text" id="new_value" name="new_value" class="mt-1 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-yellow-200" placeholder="Inserisci valore">
                            </div>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>
    <script src="assets/js/app.js" defer></script>
</body>
</html>
