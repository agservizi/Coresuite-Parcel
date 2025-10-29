<?php
// Data access layer for pickup scheduling.

require_once __DIR__ . '/../includes/db.php';

class PickupRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function upcoming(array $filters = []): array
    {
        $sql = 'SELECT r.*, u.nome AS cliente_nome FROM ritiri r JOIN users u ON u.id = r.id_cliente';
        $conditions = [];
        $params = [];

        if (!empty($filters['stato'])) {
            $conditions[] = 'r.stato = :stato';
            $params[':stato'] = $filters['stato'];
        }

        if (!empty($filters['from_date'])) {
            $conditions[] = 'r.data_ritiro >= :from_date';
            $params[':from_date'] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $conditions[] = 'r.data_ritiro <= :to_date';
            $params[':to_date'] = $filters['to_date'];
        }

        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY r.data_ritiro ASC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->pdo->prepare('UPDATE ritiri SET stato = :stato WHERE id = :id');

        return $stmt->execute([
            ':stato' => $status,
            ':id' => $id,
        ]);
    }
}
