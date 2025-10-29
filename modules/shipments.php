<?php
// Data access layer for shipment CRUD operations.

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

class ShipmentRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function all(array $filters = []): array
    {
        $sql = 'SELECT s.*, u.nome AS cliente_nome FROM spedizioni s JOIN users u ON u.id = s.id_cliente';
        $conditions = [];
        $params = [];

        if (!empty($filters['stato'])) {
            $conditions[] = 's.stato = :stato';
            $params[':stato'] = $filters['stato'];
        }

        if (!empty($filters['search'])) {
            $conditions[] = '(s.codice LIKE :search OR s.mittente LIKE :search OR s.destinatario LIKE :search)';
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY s.created_at DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM spedizioni WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $shipment = $stmt->fetch();

        return $shipment ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO spedizioni (codice, id_cliente, mittente, destinatario, peso, dimensioni, tipo_servizio, assicurazione, stato)
            VALUES (:codice, :id_cliente, :mittente, :destinatario, :peso, :dimensioni, :tipo_servizio, :assicurazione, :stato)');

        $stmt->execute([
            ':codice' => $data['codice'],
            ':id_cliente' => $data['id_cliente'],
            ':mittente' => $data['mittente'],
            ':destinatario' => $data['destinatario'],
            ':peso' => $data['peso'],
            ':dimensioni' => $data['dimensioni'],
            ':tipo_servizio' => $data['tipo_servizio'],
            ':assicurazione' => $data['assicurazione'],
            ':stato' => $data['stato'] ?? 'In attesa',
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare('UPDATE spedizioni SET mittente = :mittente, destinatario = :destinatario, peso = :peso, dimensioni = :dimensioni,
            tipo_servizio = :tipo_servizio, assicurazione = :assicurazione, stato = :stato WHERE id = :id');

        return $stmt->execute([
            ':mittente' => $data['mittente'],
            ':destinatario' => $data['destinatario'],
            ':peso' => $data['peso'],
            ':dimensioni' => $data['dimensioni'],
            ':tipo_servizio' => $data['tipo_servizio'],
            ':assicurazione' => $data['assicurazione'],
            ':stato' => $data['stato'],
            ':id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM spedizioni WHERE id = :id');

        return $stmt->execute([':id' => $id]);
    }
}
