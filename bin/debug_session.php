<?php
/**
 * Debug utility to verify session persistence and CSRF storage.
 * Upload temporarily and visit via browser/CLI: php bin/debug_session.php
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/functions.php';

$sessionId = session_id();
$sessionPath = session_save_path();

if (!isset($_SESSION['debug_counter'])) {
    $_SESSION['debug_counter'] = 0;
}
$_SESSION['debug_counter']++;

if (!isset($_SESSION['csrf_token'])) {
    generate_csrf_token();
}

header('Content-Type: text/plain; charset=utf-8');
echo "Session ID: {$sessionId}\n";
echo "Session path: {$sessionPath}\n";
echo "Session writable: " . (is_writable($sessionPath ?: sys_get_temp_dir()) ? 'yes' : 'no') . "\n";
echo "Debug counter in session: {$_SESSION['debug_counter']}\n";
echo "Stored CSRF token: " . ($_SESSION['csrf_token'] ?? 'not set') . "\n";
echo "Session data: " . print_r($_SESSION, true) . "\n";
