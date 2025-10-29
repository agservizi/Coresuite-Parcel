<?php
/**
 * Simple migration runner that executes the SQL schema against the configured database.
 * Usage: php bin/migrate.php
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script must be run from the command line.');
}

$rootDir = dirname(__DIR__);
$schemaPath = $rootDir . '/database/schema.sql';

if (!is_readable($schemaPath)) {
    fwrite(STDERR, "Schema file not found at {$schemaPath}\n");
    exit(1);
}

require_once $rootDir . '/includes/db.php';

$sql = file_get_contents($schemaPath);
if ($sql === false) {
    fwrite(STDERR, "Unable to read schema file.\n");
    exit(1);
}

// Split statements on semicolon followed by line break/end while preserving potential delimiters.
$statements = array_filter(array_map(
    static fn (string $statement): string => trim($statement),
    preg_split('/;\s*(?:\r?\n|$)/', $sql) ?: []
));

if (!$statements) {
    fwrite(STDERR, "No SQL statements found in schema file.\n");
    exit(1);
}

try {
    foreach ($statements as $statement) {
        if ($statement === '') {
            continue;
        }
        $pdo->exec($statement);
    }
} catch (PDOException $exception) {
    fwrite(STDERR, "Migration failed: " . $exception->getMessage() . "\n");
    exit(1);
}

echo "Migration completed successfully.\n";
