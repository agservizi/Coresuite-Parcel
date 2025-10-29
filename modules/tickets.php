<?php
// Data access layer for ticket management.

require_once __DIR__ . '/../includes/db.php';

class TicketRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function all(array $filters = []): array
    {
        $sql = 'SELECT t.*, u.nome AS utente_nome, s.codice AS spedizione_codice
                FROM ticket t
                JOIN users u ON u.id = t.id_utente
                LEFT JOIN spedizioni s ON s.id = t.id_spedizione';

        $conditions = [];
        $params = [];

        if (!empty($filters['stato'])) {
            $conditions[] = 't.stato = :stato';
            $params[':stato'] = $filters['stato'];
        }

        if (!empty($filters['search'])) {
            $conditions[] = '(t.messaggio LIKE :search OR u.nome LIKE :search OR COALESCE(s.codice, "") LIKE :search)';
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if (!empty($filters['utente_id'])) {
            $conditions[] = 't.id_utente = :utente_id';
            $params[':utente_id'] = $filters['utente_id'];
        }

        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY t.created_at DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->pdo->prepare('UPDATE ticket SET stato = :stato WHERE id = :id');

        return $stmt->execute([
            ':stato' => $status,
            ':id' => $id,
        ]);
    }
}
