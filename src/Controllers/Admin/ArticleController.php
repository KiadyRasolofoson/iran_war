<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Uploader;
use App\Models\Article;
use App\Models\Category;

final class ArticleController
{
    private Article $articleModel;
    private Category $categoryModel;
    private Auth $auth;
    private Uploader $uploader;

    public function __construct(
        ?Article $articleModel = null,
        ?Category $categoryModel = null,
        ?Auth $auth = null,
        ?Uploader $uploader = null
    ) {
        $this->articleModel = $articleModel ?? new Article();
        $this->categoryModel = $categoryModel ?? new Category();
        $this->auth = $auth ?? new Auth();
        $this->uploader = $uploader ?? new Uploader();

        $this->auth->requireLogin();
    }

    public function index(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 10;
        $search = trim((string) ($_GET['q'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));
        $status = in_array($status, ['draft', 'published'], true) ? $status : null;

        $categoryId = null;
        if (isset($_GET['category_id']) && $_GET['category_id'] !== '') {
            $categoryId = (int) $_GET['category_id'];
            if ($categoryId <= 0) {
                $categoryId = null;
            }
        }

        if ($search !== '') {
            $result = $this->articleModel->searchPaginated($search, $page, $perPage, $status, $categoryId);
        } else {
            $result = $this->articleModel->listPaginated($page, $perPage, $status);
        }

        $this->render('admin/articles/index.php', [
            'articles' => $result['items'] ?? [],
            'pagination' => $result['pagination'] ?? [],
            'filters' => [
                'q' => $search,
                'status' => $status ?? '',
                'category_id' => $categoryId,
            ],
            'categories' => $this->categoryModel->list(200, 0),
            'csrfToken' => $this->auth->token(),
            'flash' => $this->pullFlash(),
        ]);
    }

    public function create(): void
    {
        $old = $this->consumeOldForm();

        $this->render('admin/articles/create.php', [
            'categories' => $this->categoryModel->list(200, 0),
            'csrfToken' => $this->auth->token(),
            'errors' => $old['errors'],
            'old' => $old['data'] ?: $this->defaultFormData(),
            'flash' => $this->pullFlash(),
        ]);
    }

    public function store(): void
    {
        $this->assertPostWithCsrf();

        $input = $this->collectInput($_POST);
        $validation = $this->validateInput($input, null);

        if (!empty($validation['errors'])) {
            $this->storeOldForm($validation['errors'], $input);
            $this->flash('error', 'Please fix the form errors.');
            $this->redirect('/admin/articles/create');
        }

        $payload = $validation['payload'];

        if (isset($_FILES['image']) && is_array($_FILES['image']) && (int) ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            try {
                $payload['image'] = $this->uploader->upload($_FILES['image'], 'articles');
            } catch (\Throwable $exception) {
                $this->storeOldForm(['image' => $exception->getMessage()], $input);
                $this->flash('error', 'Image upload failed.');
                $this->redirect('/admin/articles/create');
            }
        }

        $user = $this->auth->user();
        $payload['author_id'] = (int) ($user['id'] ?? 0);

        if ($payload['author_id'] <= 0) {
            $this->storeOldForm(['author' => 'Unable to resolve the current user.'], $input);
            $this->flash('error', 'Authentication state is invalid.');
            $this->redirect('/admin/articles/create');
        }

        $createdId = $this->articleModel->create($payload);

        $this->flash('success', 'Article created (ID: ' . $createdId . ').');
        $this->redirect('/admin/articles');
    }

    public function edit($id): void
    {
        $articleId = (int) $id;
        $article = $this->articleModel->findById($articleId);

        if ($article === null) {
            $this->abortNotFound('Article not found.');
        }

        $old = $this->consumeOldForm();
        $formData = $old['data'] ?: $this->mapArticleToFormData($article);

        $this->render('admin/articles/edit.php', [
            'article' => $article,
            'categories' => $this->categoryModel->list(200, 0),
            'csrfToken' => $this->auth->token(),
            'errors' => $old['errors'],
            'old' => $formData,
            'flash' => $this->pullFlash(),
        ]);
    }

    public function update($id): void
    {
        $this->assertPostWithCsrf();

        $articleId = (int) $id;
        $article = $this->articleModel->findById($articleId);

        if ($article === null) {
            $this->abortNotFound('Article not found.');
        }

        $input = $this->collectInput($_POST);
        $validation = $this->validateInput($input, $articleId);

        if (!empty($validation['errors'])) {
            $this->storeOldForm($validation['errors'], $input);
            $this->flash('error', 'Please fix the form errors.');
            $this->redirect('/admin/articles/' . $articleId . '/edit');
        }

        $payload = $validation['payload'];

        if (isset($_POST['remove_image']) && $_POST['remove_image'] === '1') {
            $payload['image'] = null;
        }

        if (isset($_FILES['image']) && is_array($_FILES['image']) && (int) ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            try {
                $payload['image'] = $this->uploader->upload($_FILES['image'], 'articles');
            } catch (\Throwable $exception) {
                $this->storeOldForm(['image' => $exception->getMessage()], $input);
                $this->flash('error', 'Image upload failed.');
                $this->redirect('/admin/articles/' . $articleId . '/edit');
            }
        }

        $updated = $this->articleModel->update($articleId, $payload);

        if ($updated) {
            $this->flash('success', 'Article updated successfully.');
        } else {
            $this->flash('error', 'No changes were applied.');
        }

        $this->redirect('/admin/articles/' . $articleId . '/edit');
    }

    public function delete($id): void
    {
        $this->assertPostWithCsrf();

        $articleId = (int) $id;
        $deleted = $this->articleModel->delete($articleId);

        if ($deleted) {
            $this->flash('success', 'Article deleted successfully.');
        } else {
            $this->flash('error', 'Unable to delete article or article not found.');
        }

        $this->redirect('/admin/articles');
    }

    public function toggleStatus($id): void
    {
        $this->assertPostWithCsrf();

        $articleId = (int) $id;
        $article = $this->articleModel->findById($articleId);

        if ($article === null) {
            $this->flash('error', 'Article not found.');
            $this->redirect('/admin/articles');
        }

        $currentStatus = (string) ($article['status'] ?? 'draft');
        $nextStatus = $currentStatus === 'published' ? 'draft' : 'published';

        $payload = ['status' => $nextStatus];
        if ($nextStatus === 'published') {
            $payload['published_at'] = date('Y-m-d H:i:s');
        } else {
            $payload['published_at'] = null;
        }

        $updated = $this->articleModel->update($articleId, $payload);

        if ($updated) {
            $this->flash('success', 'Status updated to: ' . $nextStatus . '.');
        } else {
            $this->flash('error', 'Unable to toggle status.');
        }

        $this->redirect('/admin/articles');
    }

    private function collectInput(array $input): array
    {
        return [
            'title' => trim((string) ($input['title'] ?? '')),
            'slug' => trim((string) ($input['slug'] ?? '')),
            'excerpt' => trim((string) ($input['excerpt'] ?? '')),
            'content' => trim((string) ($input['content'] ?? '')),
            'image_alt' => trim((string) ($input['image_alt'] ?? '')),
            'meta_title' => trim((string) ($input['meta_title'] ?? '')),
            'meta_description' => trim((string) ($input['meta_description'] ?? '')),
            'status' => trim((string) ($input['status'] ?? 'draft')),
            'category_id' => trim((string) ($input['category_id'] ?? '')),
            'published_at' => trim((string) ($input['published_at'] ?? '')),
        ];
    }

    private function validateInput(array $input, ?int $articleId): array
    {
        $errors = [];

        $title = $input['title'];
        if ($title === '') {
            $errors['title'] = 'Title is required.';
        } elseif (mb_strlen($title) > 255) {
            $errors['title'] = 'Title must be at most 255 characters.';
        }

        $slug = $input['slug'] !== '' ? $this->slugify($input['slug']) : $this->slugify($title);
        if ($slug === '') {
            $errors['slug'] = 'Unable to generate a valid slug.';
        } elseif (mb_strlen($slug) > 255) {
            $errors['slug'] = 'Slug must be at most 255 characters.';
        } else {
            $slug = $this->buildUniqueSlug($slug, $articleId);
        }

        $content = $input['content'];
        if ($content === '') {
            $errors['content'] = 'Content is required.';
        }

        $status = $input['status'];
        if (!in_array($status, ['draft', 'published'], true)) {
            $errors['status'] = 'Status is invalid.';
        }

        $categoryId = null;
        if ($input['category_id'] !== '') {
            $categoryId = (int) $input['category_id'];
            if ($categoryId <= 0 || $this->categoryModel->findById($categoryId) === null) {
                $errors['category_id'] = 'Category is invalid.';
            }
        }

        $publishedAt = null;
        if ($input['published_at'] !== '') {
            $publishedAt = $this->normalizeDateTimeInput($input['published_at']);
            if ($publishedAt === null) {
                $errors['published_at'] = 'Published date must use a valid date-time format.';
            }
        }

        if ($status === 'published' && $publishedAt === null) {
            $publishedAt = date('Y-m-d H:i:s');
        }

        if (mb_strlen($input['meta_title']) > 70) {
            $errors['meta_title'] = 'Meta title must be at most 70 characters.';
        }

        if (mb_strlen($input['meta_description']) > 160) {
            $errors['meta_description'] = 'Meta description must be at most 160 characters.';
        }

        if (mb_strlen($input['image_alt']) > 255) {
            $errors['image_alt'] = 'Image alt must be at most 255 characters.';
        }

        $payload = [
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $input['excerpt'] !== '' ? $input['excerpt'] : null,
            'content' => $content,
            'image_alt' => $input['image_alt'] !== '' ? $input['image_alt'] : null,
            'meta_title' => $input['meta_title'] !== '' ? $input['meta_title'] : null,
            'meta_description' => $input['meta_description'] !== '' ? $input['meta_description'] : null,
            'status' => $status,
            'category_id' => $categoryId,
            'published_at' => $publishedAt,
        ];

        return [
            'errors' => $errors,
            'payload' => $payload,
        ];
    }

    private function normalizeDateTimeInput(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $formats = ['Y-m-d\\TH:i', 'Y-m-d H:i:s', 'Y-m-d H:i'];

        foreach ($formats as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $value);
            if ($date instanceof \DateTimeImmutable) {
                return $date->format('Y-m-d H:i:s');
            }
        }

        return null;
    }

    private function buildUniqueSlug(string $baseSlug, ?int $articleId): string
    {
        $candidate = $baseSlug;
        $suffix = 2;

        while (true) {
            $existing = $this->articleModel->findBySlug($candidate);
            if ($existing === null) {
                return $candidate;
            }

            if ($articleId !== null && (int) ($existing['id'] ?? 0) === $articleId) {
                return $candidate;
            }

            $candidate = $baseSlug . '-' . $suffix;
            $suffix++;

            if ($suffix > 1000) {
                return $baseSlug . '-' . time();
            }
        }
    }

    private function slugify(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($transliterated !== false) {
            $value = $transliterated;
        }

        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';

        return trim($value, '-');
    }

    private function mapArticleToFormData(array $article): array
    {
        $publishedAt = '';
        if (!empty($article['published_at']) && is_string($article['published_at'])) {
            $dt = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $article['published_at']);
            if ($dt instanceof \DateTimeImmutable) {
                $publishedAt = $dt->format('Y-m-d\\TH:i');
            }
        }

        return [
            'title' => (string) ($article['title'] ?? ''),
            'slug' => (string) ($article['slug'] ?? ''),
            'excerpt' => (string) ($article['excerpt'] ?? ''),
            'content' => (string) ($article['content'] ?? ''),
            'image_alt' => (string) ($article['image_alt'] ?? ''),
            'meta_title' => (string) ($article['meta_title'] ?? ''),
            'meta_description' => (string) ($article['meta_description'] ?? ''),
            'status' => (string) ($article['status'] ?? 'draft'),
            'category_id' => (string) ($article['category_id'] ?? ''),
            'published_at' => $publishedAt,
        ];
    }

    private function defaultFormData(): array
    {
        return [
            'title' => '',
            'slug' => '',
            'excerpt' => '',
            'content' => '',
            'image_alt' => '',
            'meta_title' => '',
            'meta_description' => '',
            'status' => 'draft',
            'category_id' => '',
            'published_at' => '',
        ];
    }

    private function storeOldForm(array $errors, array $data): void
    {
        $_SESSION['admin_articles_form'] = [
            'errors' => $errors,
            'data' => $data,
        ];
    }

    private function consumeOldForm(): array
    {
        $default = ['errors' => [], 'data' => []];

        $stored = $_SESSION['admin_articles_form'] ?? null;
        unset($_SESSION['admin_articles_form']);

        if (!is_array($stored)) {
            return $default;
        }

        return [
            'errors' => is_array($stored['errors'] ?? null) ? $stored['errors'] : [],
            'data' => is_array($stored['data'] ?? null) ? $stored['data'] : [],
        ];
    }

    private function assertPostWithCsrf(): void
    {
        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

        if ($method !== 'POST') {
            http_response_code(405);
            header('Content-Type: text/plain; charset=UTF-8');
            echo '405 Method Not Allowed';
            exit;
        }

        if (!$this->auth->verifyToken()) {
            http_response_code(419);
            header('Content-Type: text/plain; charset=UTF-8');
            echo '419 CSRF Token Mismatch';
            exit;
        }
    }

    private function flash(string $type, string $message): void
    {
        $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
    }

    private function pullFlash(): array
    {
        $messages = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);

        return is_array($messages) ? $messages : [];
    }

    private function render(string $view, array $data = []): void
    {
        $viewPath = APP_ROOT . '/views/' . ltrim($view, '/');

        if (!is_readable($viewPath)) {
            throw new \RuntimeException('View not found: ' . $viewPath);
        }

        extract($data, EXTR_SKIP);
        require $viewPath;
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }

    private function abortNotFound(string $message): void
    {
        http_response_code(404);
        header('Content-Type: text/plain; charset=UTF-8');
        echo $message;
        exit;
    }
}
