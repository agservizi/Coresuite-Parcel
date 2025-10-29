<?php
// Shared utility helpers for sanitization, tokens, and formatting.

if (!function_exists('sanitize_input')) {
    function sanitize_input(?string $value): string
    {
        return htmlspecialchars(trim((string) $value), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('generate_csrf_token')) {
    function generate_csrf_token(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time();

        return $token;
    }
}

if (!function_exists('validate_csrf_token')) {
    function validate_csrf_token(?string $token, int $ttlSeconds = 900): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $storedToken = $_SESSION['csrf_token'] ?? null;
        $issuedAt = $_SESSION['csrf_token_time'] ?? 0;

        $isValid = is_string($token)
            && hash_equals((string) $storedToken, (string) $token)
            && (time() - (int) $issuedAt) <= $ttlSeconds;

        if ($isValid) {
            unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
        }

        return $isValid;
    }
}

if (!function_exists('format_currency')) {
    function format_currency(float $amount, string $currency = 'EUR'): string
    {
        return number_format($amount, 2, ',', '.') . " {$currency}";
    }
}
