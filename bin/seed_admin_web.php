<?php
/**
 * Web-safe admin seeder. Upload temporarily, call via browser once, then delete.
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/db.php';

$adminEmail = 'admin@coresuite.it';
$passwordPlain = 'admin';
$message = '';

try {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $adminEmail]);

    $row = $stmt->fetch();

    $passwordHash = password_hash($passwordPlain, PASSWORD_DEFAULT);

    if ($row) {
        $update = $pdo->prepare('UPDATE users SET nome = :nome, password = :password, ruolo = :ruolo WHERE id = :id');
        $update->execute([
            ':nome' => 'admin',
            ':password' => $passwordHash,
            ':ruolo' => 'Admin',
            ':id' => $row['id'],
        ]);
        $message = 'Admin user updated successfully (' . htmlspecialchars($adminEmail, ENT_QUOTES, 'UTF-8') . ').';
    } else {
        $insert = $pdo->prepare('INSERT INTO users (nome, email, password, ruolo, telefono, indirizzo, iban, foto)
            VALUES (:nome, :email, :password, :ruolo, :telefono, :indirizzo, :iban, :foto)');

        $insert->execute([
            ':nome' => 'admin',
            ':email' => $adminEmail,
            ':password' => $passwordHash,
            ':ruolo' => 'Admin',
            ':telefono' => '+39 000 0000000',
            ':indirizzo' => 'Via Principale 1, Milano (MI)',
            ':iban' => null,
            ':foto' => null,
        ]);

        $message = 'Admin user created successfully with email ' . htmlspecialchars($adminEmail, ENT_QUOTES, 'UTF-8') . '.';
    }
} catch (Throwable $throwable) {
    http_response_code(500);
    $message = 'Seeder error: ' . htmlspecialchars($throwable->getMessage(), ENT_QUOTES, 'UTF-8');
}

?><!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Seed Admin</title>
    <style>
        body { font-family: system-ui, sans-serif; padding: 2rem; color: #0f172a; background: #f8fafc; }
        .box { max-width: 480px; margin: 0 auto; padding: 1.5rem; border-radius: 1rem; background: #fff; box-shadow: 0 10px 25px rgba(15,23,42,0.1); }
        .box h1 { margin-top: 0; font-size: 1.5rem; }
        .success { color: #15803d; }
        .error { color: #dc2626; }
        a { display: inline-block; margin-top: 1rem; color: #2563eb; }
    </style>
</head>
<body>
    <div class="box">
        <h1>Seed Admin</h1>
        <p class="<?php echo http_response_code() >= 400 ? 'error' : 'success'; ?>"><?php echo $message; ?></p>
        <p>Ricorda di eliminare questo file (bin/seed_admin_web.php) dopo l'esecuzione.</p>
        <a href="/login.php">Vai al login</a>
    </div>
</body>
</html>
