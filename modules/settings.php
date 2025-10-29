<?php
// Data access layer for application settings.

require_once __DIR__ . '/../includes/db.php';

class SettingsRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM settings ORDER BY chiave');

        return $stmt->fetchAll();
    }

    public function upsert(string $key, ?string $value): bool
    {
        $stmt = $this->pdo->prepare('INSERT INTO settings (chiave, valore) VALUES (:chiave, :valore)
            ON DUPLICATE KEY UPDATE valore = VALUES(valore), updated_at = CURRENT_TIMESTAMP');

        return $stmt->execute([
            ':chiave' => $key,
            ':valore' => $value,
        ]);
    }
}
