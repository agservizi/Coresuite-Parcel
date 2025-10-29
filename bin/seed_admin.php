<?php
/**
 * Seed script to create an initial admin user.
 * Usage: php bin/seed_admin.php
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script must be run from the command line.');
}

$rootDir = dirname(__DIR__);
require_once $rootDir . '/includes/db.php';
require_once $rootDir . '/includes/functions.php';

$adminEmail = 'admin@coresuite.it';
$existsStmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
$existsStmt->execute([':email' => $adminEmail]);

if ($existsStmt->fetch()) {
    echo "Admin user already exists ({$adminEmail}).\n";
    exit(0);
}

$passwordHash = password_hash('Admin123!', PASSWORD_DEFAULT);

$insert = $pdo->prepare('INSERT INTO users (nome, email, password, ruolo, telefono, indirizzo, iban, foto)
    VALUES (:nome, :email, :password, :ruolo, :telefono, :indirizzo, :iban, :foto)');

$insert->execute([
    ':nome' => 'Admin Coresuite',
    ':email' => $adminEmail,
    ':password' => $passwordHash,
    ':ruolo' => 'Admin',
    ':telefono' => '+39 000 0000000',
    ':indirizzo' => 'Via Principale 1, Milano (MI)',
    ':iban' => null,
    ':foto' => null,
]);

echo "Admin user created successfully.\n";
