<?php
// Label generation service placeholder. Integrate with TCPDF or FPDF later.

require_once __DIR__ . '/../includes/db.php';

class LabelService
{
    public function __construct(private PDO $pdo)
    {
    }

    public function getLabelData(int $shipmentId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT s.*, u.nome AS cliente_nome FROM spedizioni s JOIN users u ON u.id = s.id_cliente WHERE s.id = :id');
        $stmt->execute([':id' => $shipmentId]);
        $label = $stmt->fetch();

        return $label ?: null;
    }

    public function generatePdf(array $labelData): string
    {
        $filePath = __DIR__ . '/../pdf/etichetta_' . $labelData['id'] . '.pdf';
        // TODO: Implement PDF generation using TCPDF/FPDF.
        file_put_contents($filePath, 'PDF generation placeholder.');

        return $filePath;
    }
}
