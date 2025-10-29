<?php
// Authentication helpers for login status, role checking, and session management.

if (session_status() !== PHP_SESSION_ACTIVE) {
    $sessionPath = dirname(__DIR__) . '/storage/sessions';

    if (!is_dir($sessionPath)) {
        if (!mkdir($sessionPath, 0775, true) && !is_dir($sessionPath)) {
            error_log('Unable to create session directory at ' . $sessionPath);
        }
    }

    if (is_dir($sessionPath) && is_writable($sessionPath)) {
        session_save_path($sessionPath);
    } else {
        error_log('Session directory is not writable: ' . $sessionPath);
    }

    session_name('coresuite_session');
    session_start();
}

const SESSION_TIMEOUT_SECONDS = 1800;

function ensure_session_freshness(): void
{
    $lastActivity = $_SESSION['last_activity'] ?? time();
    if ((time() - $lastActivity) > SESSION_TIMEOUT_SECONDS) {
        logout_user();
        header('Location: login.php?timeout=1');
        exit;
    }

    $_SESSION['last_activity'] = time();
}

function login_user(array $user): void
{
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_role'] = $user['ruolo'];
    $_SESSION['last_activity'] = time();
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function require_role(array $allowedRoles): void
{
    if (!is_user_authenticated() || !in_array($_SESSION['user_role'], $allowedRoles, true)) {
        http_response_code(403);
        exit('Access denied.');
    }
}

function is_user_authenticated(): bool
{
    return isset($_SESSION['user_id']);
}

function current_user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function current_user_role(): ?string
{
    return $_SESSION['user_role'] ?? null;
}
