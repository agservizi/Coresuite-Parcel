<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$error = null;
$failureReason = null;
$debugMode = filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN);

if (!function_exists('log_login_failure')) {
    function log_login_failure(?string $reason, string $email): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $message = sprintf('Login failed (%s) for %s from %s | UA: %s', $reason ?? 'unknown', $email, $ip, $userAgent);
        error_log($message);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email'] ?? '');

    if (!validate_csrf_token($_POST['csrf_token'] ?? null)) {
        $error = 'Sessione scaduta. Riprova.';
        $failureReason = 'csrf_invalid';
        log_login_failure($failureReason, $email);
    } else {
        $password = $_POST['password'] ?? '';

        try {
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if (!$user) {
                $failureReason = 'user_not_found';
            } elseif (!password_verify($password, $user['password'])) {
                $failureReason = 'password_mismatch';
            } else {
                login_user($user);
                header('Location: dashboard.php');
                exit;
            }
        } catch (Throwable $exception) {
            $failureReason = 'db_error';
            error_log('Login query error: ' . $exception->getMessage());
        }

        if ($failureReason !== 'db_error') {
            $error = 'Credenziali non valide.';
        } else {
            $error = 'Servizio temporaneamente non disponibile. Riprova più tardi.';
        }

        log_login_failure($failureReason, $email);
    }
}

$csrfToken = generate_csrf_token();
if ($error && $debugMode && $failureReason) {
    $error .= ' [' . $failureReason . ']';
}
?>
<!DOCTYPE html>
<html lang="it" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Coresuite Parcel</title>
    <link rel="stylesheet" href="assets/css/build.css">
</head>
<body class="min-h-screen flex items-center justify-center bg-slate-100 dark:bg-slate-950 text-slate-900 dark:text-slate-100">
    <div class="w-full max-w-md p-6 card">
        <div class="flex flex-col items-center gap-2 mb-6">
            <div class="h-12 w-12 rounded-full bg-[var(--coresuite-primary)] flex items-center justify-center font-bold text-slate-900">CP</div>
            <h1 class="text-2xl font-semibold">Accesso riservato</h1>
            <p class="text-sm text-center text-slate-500">Inserisci le credenziali per accedere al tuo pannello.</p>
        </div>
        <?php if ($error) : ?>
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
            <div class="space-y-2">
                <label for="email" class="text-sm font-medium">Email</label>
                <input type="email" id="email" name="email" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-yellow-200">
            </div>
            <div class="space-y-2">
                <label for="password" class="text-sm font-medium">Password</label>
                <input type="password" id="password" name="password" required class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-yellow-200">
            </div>
            <div class="flex items-center justify-between text-sm">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-[var(--coresuite-primary)] focus:ring-yellow-200">
                    Ricordami
                </label>
                <a href="#" class="text-slate-500 hover:text-slate-700">Password dimenticata?</a>
            </div>
            <button type="submit" class="w-full btn-primary">Accedi</button>
        </form>
    </div>
    <script src="assets/js/app.js" defer></script>
</body>
</html>
