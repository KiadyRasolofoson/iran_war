<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class User
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getInstance()->getConnection();
    }

    public function list(int $limit = 50, int $offset = 0): array
    {
        $sql = 'SELECT * FROM users ORDER BY created_at DESC LIMIT :limit OFFSET :offset';
        $statement = $this->db->prepare($sql);
        $statement->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $statement->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();
        $user = $statement->fetch();

        return $user !== false ? $user : null;
    }

    public function findByUsername(string $username): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
        $statement->bindValue(':username', $username);
        $statement->execute();
        $user = $statement->fetch();

        return $user !== false ? $user : null;
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO users (username, email, password, role)
            VALUES (:username, :email, :password, :role)';

        $statement = $this->db->prepare($sql);
        $statement->bindValue(':username', (string) ($data['username'] ?? ''));
        $statement->bindValue(':email', (string) ($data['email'] ?? ''));
        $statement->bindValue(':password', (string) ($data['password'] ?? ''));
        $statement->bindValue(':role', (string) ($data['role'] ?? 'editor'));
        $statement->execute();

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $allowedFields = ['username', 'email', 'password', 'role'];
        $setParts = [];
        $params = [':id' => $id];

        foreach ($allowedFields as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }

            $placeholder = ':' . $field;
            $setParts[] = $field . ' = ' . $placeholder;
            $params[$placeholder] = $data[$field];
        }

        if ($setParts === []) {
            return false;
        }

        $sql = 'UPDATE users SET ' . implode(', ', $setParts) . ' WHERE id = :id';
        $statement = $this->db->prepare($sql);

        foreach ($params as $placeholder => $value) {
            if ($placeholder === ':id') {
                $statement->bindValue($placeholder, (int) $value, PDO::PARAM_INT);
                continue;
            }

            $statement->bindValue($placeholder, (string) $value);
        }

        $statement->execute();

        return $statement->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $statement = $this->db->prepare('DELETE FROM users WHERE id = :id');
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        return $statement->rowCount() > 0;
    }
}
