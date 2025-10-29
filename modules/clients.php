<?php
// Data access layer for client management.

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

class ClientRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function all(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM users WHERE ruolo = "Cliente" ORDER BY created_at DESC');

        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id AND ruolo = "Cliente"');
        $stmt->execute([':id' => $id]);
        $client = $stmt->fetch();

        return $client ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO users (nome, email, password, ruolo, telefono, indirizzo, iban)
            VALUES (:nome, :email, :password, "Cliente", :telefono, :indirizzo, :iban)');

        $stmt->execute([
            ':nome' => $data['nome'],
            ':email' => $data['email'],
            ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
            ':telefono' => $data['telefono'],
            ':indirizzo' => $data['indirizzo'],
            ':iban' => $data['iban'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare('UPDATE users SET nome = :nome, email = :email, telefono = :telefono, indirizzo = :indirizzo, iban = :iban WHERE id = :id');

        return $stmt->execute([
            ':nome' => $data['nome'],
            ':email' => $data['email'],
            ':telefono' => $data['telefono'],
            ':indirizzo' => $data['indirizzo'],
            ':iban' => $data['iban'],
            ':id' => $id,
        ]);
    }
}
