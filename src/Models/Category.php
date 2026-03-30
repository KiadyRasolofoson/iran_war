<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Category
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getInstance()->getConnection();
    }

    public function list(int $limit = 100, int $offset = 0): array
    {
        $sql = 'SELECT * FROM categories ORDER BY name ASC LIMIT :limit OFFSET :offset';
        $statement = $this->db->prepare($sql);
        $statement->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $statement->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM categories WHERE id = :id LIMIT 1');
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();
        $category = $statement->fetch();

        return $category !== false ? $category : null;
    }

    public function findBySlug(string $slug): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM categories WHERE slug = :slug LIMIT 1');
        $statement->bindValue(':slug', $slug);
        $statement->execute();
        $category = $statement->fetch();

        return $category !== false ? $category : null;
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO categories (name, slug, description, seo_title, seo_description, status)
                VALUES (:name, :slug, :description, :seo_title, :seo_description, :status)';

        $statement = $this->db->prepare($sql);
        $statement->bindValue(':name', (string) ($data['name'] ?? ''));
        $statement->bindValue(':slug', (string) ($data['slug'] ?? ''));
        $statement->bindValue(':description', $data['description'] ?? null, $data['description'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':seo_title', $data['seo_title'] ?? null, $data['seo_title'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':seo_description', $data['seo_description'] ?? null, $data['seo_description'] === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $statement->bindValue(':status', (string) ($data['status'] ?? 'active'));
        $statement->execute();

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $allowedFields = ['name', 'slug', 'description', 'seo_title', 'seo_description', 'status'];
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

        $sql = 'UPDATE categories SET ' . implode(', ', $setParts) . ' WHERE id = :id';
        $statement = $this->db->prepare($sql);

        foreach ($params as $placeholder => $value) {
            if ($placeholder === ':id') {
                $statement->bindValue($placeholder, (int) $value, PDO::PARAM_INT);
                continue;
            }

            if ($value === null) {
                $statement->bindValue($placeholder, null, PDO::PARAM_NULL);
                continue;
            }

            $statement->bindValue($placeholder, (string) $value, PDO::PARAM_STR);
        }

        $statement->execute();

        return $statement->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $statement = $this->db->prepare('DELETE FROM categories WHERE id = :id');
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        return $statement->rowCount() > 0;
    }

    public function listWithPublishedArticleCount(): array
    {
        $sql = '
            SELECT c.*, COUNT(a.id) AS article_count
            FROM categories c
            LEFT JOIN articles a ON a.category_id = c.id AND a.status = :status
            WHERE c.status = :category_status
            GROUP BY c.id
            ORDER BY c.name ASC
        ';

        $statement = $this->db->prepare($sql);
        $statement->bindValue(':status', 'published');
        $statement->bindValue(':category_status', 'active');
        $statement->execute();

        return $statement->fetchAll();
    }
}
