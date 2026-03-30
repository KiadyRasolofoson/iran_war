<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Models\Category;

final class CategoryController
{
    private Category $categoryModel;
    private Auth $auth;

    public function __construct(?Category $categoryModel = null, ?Auth $auth = null)
    {
        $this->categoryModel = $categoryModel ?? new Category();
        $this->auth = $auth ?? new Auth();
    }

    public function index(): void
    {
        $this->auth->requireLogin();

        $categories = $this->categoryModel->list(500, 0);

        $this->render('admin/categories/index', [
            'categories' => $categories,
            'flashSuccess' => $this->pullFlash('success'),
            'flashError' => $this->pullFlash('error'),
            'csrfToken' => $this->auth->token(),
        ], 'Gestion des categories');
    }

    public function create(): void
    {
        $this->auth->requireLogin();

        $this->render('admin/categories/create', [
            'errors' => [],
            'old' => [
                'name' => '',
                'slug' => '',
                'description' => '',
                'seo_title' => '',
                'seo_description' => '',
                'status' => 'active',
            ],
            'csrfToken' => $this->auth->token(),
        ], 'Creer une categorie');
    }

    public function store(): void
    {
        $this->auth->requireLogin();
        $this->requirePostAndCsrf();

        $payload = $this->categoryPayloadFromRequest();
        $errors = $this->validateCategoryPayload($payload);

        $existing = $this->categoryModel->findBySlug($payload['slug']);
        if ($existing !== null) {
            $errors[] = 'Le slug est deja utilise.';
        }

        if ($errors !== []) {
            http_response_code(422);
            $this->render('admin/categories/create', [
                'errors' => $errors,
                'old' => $payload,
                'csrfToken' => $this->auth->token(),
            ], 'Creer une categorie');

            return;
        }

        $this->categoryModel->create($payload);
        $this->flash('success', 'Categorie creee avec succes.');
        $this->redirect('/admin/categories');
    }

    public function edit(string $id): void
    {
        $this->auth->requireLogin();

        $categoryId = (int) $id;
        $category = $this->categoryModel->findById($categoryId);

        if ($category === null) {
            $this->notFound('Categorie introuvable.');
        }

        $this->render('admin/categories/edit', [
            'errors' => [],
            'category' => $category,
            'csrfToken' => $this->auth->token(),
        ], 'Modifier une categorie');
    }

    public function update(string $id): void
    {
        $this->auth->requireLogin();
        $this->requirePostAndCsrf();

        $categoryId = (int) $id;
        $category = $this->categoryModel->findById($categoryId);

        if ($category === null) {
            $this->notFound('Categorie introuvable.');
        }

        $payload = $this->categoryPayloadFromRequest();
        $errors = $this->validateCategoryPayload($payload);

        $existing = $this->categoryModel->findBySlug($payload['slug']);
        if ($existing !== null && (int) $existing['id'] !== $categoryId) {
            $errors[] = 'Le slug est deja utilise.';
        }

        if ($errors !== []) {
            http_response_code(422);
            $this->render('admin/categories/edit', [
                'errors' => $errors,
                'category' => array_merge($category, $payload),
                'csrfToken' => $this->auth->token(),
            ], 'Modifier une categorie');

            return;
        }

        $this->categoryModel->update($categoryId, $payload);
        $this->flash('success', 'Categorie mise a jour.');
        $this->redirect('/admin/categories');
    }

    public function delete(string $id): void
    {
        $this->auth->requireLogin();
        $this->requirePostAndCsrf();

        $categoryId = (int) $id;
        $this->categoryModel->delete($categoryId);

        $this->flash('success', 'Categorie supprimee.');
        $this->redirect('/admin/categories');
    }

    private function categoryPayloadFromRequest(): array
    {
        $name = trim((string) ($_POST['name'] ?? ''));
        $slug = trim((string) ($_POST['slug'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $seoTitle = trim((string) ($_POST['seo_title'] ?? ''));
        $seoDescription = trim((string) ($_POST['seo_description'] ?? ''));
        $status = trim((string) ($_POST['status'] ?? 'active'));

        if ($slug === '' && $name !== '') {
            $slug = $this->slugify($name);
        }

        return [
            'name' => $name,
            'slug' => $slug,
            'description' => $description === '' ? null : $description,
            'seo_title' => $seoTitle === '' ? null : $seoTitle,
            'seo_description' => $seoDescription === '' ? null : $seoDescription,
            'status' => $status,
        ];
    }

    private function validateCategoryPayload(array $payload): array
    {
        $errors = [];

        if ((string) $payload['name'] === '') {
            $errors[] = 'Le nom est obligatoire.';
        }

        if ((string) $payload['slug'] === '') {
            $errors[] = 'Le slug est obligatoire.';
        }

        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', (string) $payload['slug'])) {
            $errors[] = 'Le slug doit contenir uniquement des lettres minuscules, chiffres et tirets.';
        }

        $allowedStatus = ['active', 'hidden'];
        if (!in_array((string) $payload['status'], $allowedStatus, true)) {
            $errors[] = 'Le statut selectionne est invalide.';
        }

        return $errors;
    }

    private function requirePostAndCsrf(): void
    {
        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        if ($method !== 'POST') {
            http_response_code(405);
            header('Content-Type: text/plain; charset=UTF-8');
            echo '405 Method Not Allowed';
            exit;
        }

        if ($this->auth->verifyToken() === false) {
            http_response_code(419);
            header('Content-Type: text/plain; charset=UTF-8');
            echo '419 CSRF token mismatch';
            exit;
        }
    }

    private function render(string $view, array $data = [], string $title = 'Administration'): void
    {
        $viewPath = APP_ROOT . '/views/' . $view . '.php';
        $layoutPath = APP_ROOT . '/views/admin/layout.php';

        if (!is_readable($viewPath) || !is_readable($layoutPath)) {
            throw new \RuntimeException('View not found: ' . $viewPath);
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $viewPath;
        $content = (string) ob_get_clean();

        $showAdminNav = true;
        $authUser = $this->auth->user();
        $csrfToken = $this->auth->token();

        require $layoutPath;
    }

    private function redirect(string $location): void
    {
        http_response_code(302);
        header('Location: ' . $location);
        exit;
    }

    private function notFound(string $message): void
    {
        http_response_code(404);
        header('Content-Type: text/plain; charset=UTF-8');
        echo $message;
        exit;
    }

    private function flash(string $key, string $message): void
    {
        $_SESSION['flash'][$key] = $message;
    }

    private function pullFlash(string $key): ?string
    {
        if (!isset($_SESSION['flash'][$key]) || !is_string($_SESSION['flash'][$key])) {
            return null;
        }

        $message = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);

        return $message;
    }

    private function slugify(string $value): string
    {
        $slug = strtolower(trim($value));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';

        return trim($slug, '-');
    }
}
