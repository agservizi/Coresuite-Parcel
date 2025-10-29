<?php
// Invoice generation and retrieval service.

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

class InvoiceService
{
    public function __construct(private PDO $pdo)
    {
    }

    public function allForClient(int $clientId): array
    {
        $stmt = $this->pdo->prepare('SELECT f.*, s.codice AS spedizione_codice FROM fatture f JOIN spedizioni s ON s.id = f.id_spedizione WHERE s.id_cliente = :client ORDER BY f.created_at DESC');
        $stmt->execute([':client' => $clientId]);

        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO fatture (id_spedizione, totale, iva, pdf_path) VALUES (:id_spedizione, :totale, :iva, :pdf_path)');
        $stmt->execute([
            ':id_spedizione' => $data['id_spedizione'],
            ':totale' => $data['totale'],
            ':iva' => $data['iva'],
            ':pdf_path' => $data['pdf_path'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}
