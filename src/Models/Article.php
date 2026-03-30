<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

final class Article
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::getInstance()->getConnection();
    }

    public function listPaginated(int $page = 1, int $perPage = 10, ?string $status = null): array
    {
        [$page, $perPage, $offset] = $this->normalizePagination($page, $perPage);

        $whereSql = '';
        $params = [];

        if ($status !== null && $status !== '') {
            $whereSql = ' WHERE a.status = :status';
            $params[':status'] = $status;
        }

        $countSql = 'SELECT COUNT(*) FROM articles a' . $whereSql;
        $total = $this->countFromQuery($countSql, $params);

        $sql =
            'SELECT
                a.*,
                c.name AS category_name,
                c.slug AS category_slug,
                u.username AS author_username
            FROM articles a
            LEFT JOIN categories c ON c.id = a.category_id
            INNER JOIN users u ON u.id = a.author_id' .
            $whereSql .
            ' ORDER BY a.published_at DESC, a.created_at DESC
              LIMIT :limit OFFSET :offset';

        $statement = $this->db->prepare($sql);

        foreach ($params as $placeholder => $value) {
            $statement->bindValue($placeholder, $value, PDO::PARAM_STR);
        }

        $statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return $this->buildPaginatedResult($statement->fetchAll(), $page, $perPage, $total);
    }

    public function searchPaginated(
        string $search,
        int $page = 1,
        int $perPage = 10,
        ?string $status = null,
        ?int $categoryId = null
    ): array {
        [$page, $perPage, $offset] = $this->normalizePagination($page, $perPage);

        $searchLike = '%' . $search . '%';
        $where = ['(a.title LIKE :search_title OR a.excerpt LIKE :search_excerpt OR a.content LIKE :search_content)'];
        $params = [
            ':search_title' => $searchLike,
            ':search_excerpt' => $searchLike,
            ':search_content' => $searchLike,
        ];

        if ($status !== null && $status !== '') {
            $where[] = 'a.status = :status';
            $params[':status'] = $status;
        }

        if ($categoryId !== null) {
            $where[] = 'a.category_id = :category_id';
            $params[':category_id'] = $categoryId;
        }

        $whereSql = ' WHERE ' . implode(' AND ', $where);

        $countSql = 'SELECT COUNT(*) FROM articles a' . $whereSql;
        $total = $this->countFromQuery($countSql, $params);

        $sql =
            'SELECT
                a.*,
                c.name AS category_name,
                c.slug AS category_slug,
                u.username AS author_username
            FROM articles a
            LEFT JOIN categories c ON c.id = a.category_id
            INNER JOIN users u ON u.id = a.author_id' .
            $whereSql .
            ' ORDER BY a.published_at DESC, a.created_at DESC
              LIMIT :limit OFFSET :offset';

        $statement = $this->db->prepare($sql);

        foreach ($params as $placeholder => $value) {
            if ($placeholder === ':category_id') {
                $statement->bindValue($placeholder, (int) $value, PDO::PARAM_INT);
                continue;
            }

            $statement->bindValue($placeholder, (string) $value, PDO::PARAM_STR);
        }

        $statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return $this->buildPaginatedResult($statement->fetchAll(), $page, $perPage, $total);
    }

    public function findById(int $id): ?array
    {
        $sql =
            'SELECT
                a.*,
                c.name AS category_name,
                c.slug AS category_slug,
                u.username AS author_username
            FROM articles a
            LEFT JOIN categories c ON c.id = a.category_id
            INNER JOIN users u ON u.id = a.author_id
            WHERE a.id = :id
            LIMIT 1';

        $statement = $this->db->prepare($sql);
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();
        $article = $statement->fetch();

        return $article !== false ? $article : null;
    }

    public function findBySlug(string $slug): ?array
    {
        $sql =
            'SELECT
                a.*,
                c.name AS category_name,
                c.slug AS category_slug,
                u.username AS author_username
            FROM articles a
            LEFT JOIN categories c ON c.id = a.category_id
            INNER JOIN users u ON u.id = a.author_id
            WHERE a.slug = :slug
            LIMIT 1';

        $statement = $this->db->prepare($sql);
        $statement->bindValue(':slug', $slug, PDO::PARAM_STR);
        $statement->execute();
        $article = $statement->fetch();

        return $article !== false ? $article : null;
    }

    public function create(array $data): int
    {
        $sql =
            'INSERT INTO articles (
                category_id,
                author_id,
                title,
                slug,
                excerpt,
                content,
                image,
                image_alt,
                meta_title,
                meta_description,
                status,
                published_at
            ) VALUES (
                :category_id,
                :author_id,
                :title,
                :slug,
                :excerpt,
                :content,
                :image,
                :image_alt,
                :meta_title,
                :meta_description,
                :status,
                :published_at
            )';

        $statement = $this->db->prepare($sql);
        $this->bindNullableInt($statement, ':category_id', $data['category_id'] ?? null);
        $statement->bindValue(':author_id', (int) ($data['author_id'] ?? 0), PDO::PARAM_INT);
        $statement->bindValue(':title', (string) ($data['title'] ?? ''), PDO::PARAM_STR);
        $statement->bindValue(':slug', (string) ($data['slug'] ?? ''), PDO::PARAM_STR);
        $this->bindNullableString($statement, ':excerpt', $data['excerpt'] ?? null);
        $statement->bindValue(':content', (string) ($data['content'] ?? ''), PDO::PARAM_STR);
        $this->bindNullableString($statement, ':image', $data['image'] ?? null);
        $this->bindNullableString($statement, ':image_alt', $data['image_alt'] ?? null);
        $this->bindNullableString($statement, ':meta_title', $data['meta_title'] ?? null);
        $this->bindNullableString($statement, ':meta_description', $data['meta_description'] ?? null);
        $statement->bindValue(':status', (string) ($data['status'] ?? 'draft'), PDO::PARAM_STR);
        $this->bindNullableString($statement, ':published_at', $data['published_at'] ?? null);
        $statement->execute();

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $allowedFields = [
            'category_id',
            'author_id',
            'title',
            'slug',
            'excerpt',
            'content',
            'image',
            'image_alt',
            'meta_title',
            'meta_description',
            'status',
            'published_at',
        ];

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

        $sql = 'UPDATE articles SET ' . implode(', ', $setParts) . ' WHERE id = :id';
        $statement = $this->db->prepare($sql);

        foreach ($params as $placeholder => $value) {
            if ($placeholder === ':id' || $placeholder === ':category_id' || $placeholder === ':author_id') {
                if ($value === null && $placeholder === ':category_id') {
                    $statement->bindValue($placeholder, null, PDO::PARAM_NULL);
                } else {
                    $statement->bindValue($placeholder, (int) $value, PDO::PARAM_INT);
                }

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
        $statement = $this->db->prepare('DELETE FROM articles WHERE id = :id');
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        return $statement->rowCount() > 0;
    }

    private function countFromQuery(string $sql, array $params): int
    {
        $statement = $this->db->prepare($sql);

        foreach ($params as $placeholder => $value) {
            if ($placeholder === ':category_id') {
                $statement->bindValue($placeholder, (int) $value, PDO::PARAM_INT);
                continue;
            }

            $statement->bindValue($placeholder, (string) $value, PDO::PARAM_STR);
        }

        $statement->execute();

        return (int) $statement->fetchColumn();
    }

    private function buildPaginatedResult(array $items, int $page, int $perPage, int $total): array
    {
        $totalPages = (int) max(1, (int) ceil($total / $perPage));

        return [
            'items' => $items,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
                'has_prev' => $page > 1,
                'has_next' => $page < $totalPages,
            ],
        ];
    }

    private function normalizePagination(int $page, int $perPage): array
    {
        $safePage = max(1, $page);
        $safePerPage = min(100, max(1, $perPage));
        $offset = ($safePage - 1) * $safePerPage;

        return [$safePage, $safePerPage, $offset];
    }

    private function bindNullableString(\PDOStatement $statement, string $placeholder, ?string $value): void
    {
        if ($value === null || $value === '') {
            $statement->bindValue($placeholder, null, PDO::PARAM_NULL);
            return;
        }

        $statement->bindValue($placeholder, $value, PDO::PARAM_STR);
    }

    private function bindNullableInt(\PDOStatement $statement, string $placeholder, ?int $value): void
    {
        if ($value === null) {
            $statement->bindValue($placeholder, null, PDO::PARAM_NULL);
            return;
        }

        $statement->bindValue($placeholder, $value, PDO::PARAM_INT);
    }
}
