<?php
// Database connection bootstrap using PDO for consistent access across modules.

$envFile = dirname(__DIR__) . '/.env';
if (is_readable($envFile)) {
    $envValues = parse_ini_file($envFile, false, INI_SCANNER_RAW);
    if (is_array($envValues)) {
        foreach ($envValues as $key => $value) {
            if ($value !== false && getenv($key) === false) {
                putenv($key . '=' . $value);
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}

$DB_HOST = getenv('DB_HOST');
$DB_NAME = getenv('DB_NAME');
$DB_USER = getenv('DB_USER');
$DB_PASSWORD = getenv('DB_PASSWORD');
$DB_CHARSET = getenv('DB_CHARSET') ?: 'utf8mb4';

if (!$DB_HOST || !$DB_NAME || !$DB_USER) {
    error_log('Missing database configuration. Please set DB_HOST, DB_NAME, and DB_USER in the .env file.');
    http_response_code(500);
    exit('Database configuration error.');
}

try {
    $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHARSET}";
    $pdo = new PDO($dsn, $DB_USER, $DB_PASSWORD, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Database connection error.');
}
