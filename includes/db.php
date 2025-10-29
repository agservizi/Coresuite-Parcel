<?php
// Database connection bootstrap using PDO for consistent access across modules.

$envFile = dirname(__DIR__) . '/.env';
if (is_readable($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || $line[0] === ';') {
            continue;
        }

        [$key, $value] = array_map('trim', explode('=', $line, 2) + [1 => '']);
        if ($key === '') {
            continue;
        }

        // Remove inline comments not wrapped in quotes.
        if ($value !== '' && $value[0] !== '"' && $value[0] !== "'") {
            $value = preg_replace('/\s*[#;].*$/', '', $value) ?? $value;
        }

        $value = trim($value, "\"' ");

        if (getenv($key) === false) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

$DB_HOST = getenv('DB_HOST');
$DB_NAME = getenv('DB_NAME');
$DB_USER = getenv('DB_USER');
$DB_PASSWORD = getenv('DB_PASSWORD');
$DB_CHARSET = getenv('DB_CHARSET') ?: 'utf8mb4';
$debugMode = filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN);

$missingKeys = array_filter([
    'DB_HOST' => $DB_HOST,
    'DB_NAME' => $DB_NAME,
    'DB_USER' => $DB_USER,
], static fn ($value): bool => $value === null || $value === '');

if ($missingKeys) {
    $missingList = implode(', ', array_keys($missingKeys));
    $message = 'Missing database configuration keys: ' . $missingList;
    error_log($message);
    http_response_code(500);
    exit($debugMode ? $message : 'Database configuration error.');
}

try {
    $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHARSET}";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASSWORD, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    $errorMessage = 'Database connection failed: ' . $e->getMessage();
    error_log($errorMessage);
    http_response_code(500);
    exit($debugMode ? $errorMessage : 'Database connection error.');
}
