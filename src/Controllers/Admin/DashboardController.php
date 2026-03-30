<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;

final class DashboardController
{
    private Auth $auth;
    private Article $articleModel;
    private Category $categoryModel;
    private User $userModel;

    public function __construct(
        ?Auth $auth = null,
        ?Article $articleModel = null,
        ?Category $categoryModel = null,
        ?User $userModel = null
    ) {
        $this->auth = $auth ?? new Auth();
        $this->articleModel = $articleModel ?? new Article();
        $this->categoryModel = $categoryModel ?? new Category();
        $this->userModel = $userModel ?? new User();
    }

    public function index(): void
    {
        $this->auth->requireLogin();

        $user = $this->auth->user();
        $role = strtolower((string) ($user['role'] ?? ''));

        if (!in_array($role, ['admin', 'editor'], true)) {
            http_response_code(403);
            header('Content-Type: text/plain; charset=UTF-8');
            echo '403 Forbidden';
            return;
        }

        $articlePage = $this->articleModel->listPaginated(1, 1, null);
        $latestPage = $this->articleModel->listPaginated(1, 5, null);
        $categories = $this->categoryModel->list(1000000, 0);
        $users = $this->userModel->list(1000000, 0);

        $stats = [
            'articles' => (int) (($articlePage['pagination']['total'] ?? 0)),
            'categories' => count($categories),
            'users' => count($users),
        ];

        $this->render(
            'admin/dashboard',
            [
                'stats' => $stats,
                'latestArticles' => $latestPage['items'] ?? [],
            ],
            'Dashboard admin',
            true
        );
    }

    private function render(string $view, array $data, string $title, bool $showAdminNav): void
    {
        $viewPath = APP_ROOT . '/views/' . $view . '.php';
        $layoutPath = APP_ROOT . '/views/admin/layout.php';

        if (!is_readable($viewPath) || !is_readable($layoutPath)) {
            http_response_code(500);
            header('Content-Type: text/plain; charset=UTF-8');
            echo '500 View not found';
            return;
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewPath;
        $content = (string) ob_get_clean();

        $authUser = $this->auth->user();
        $csrfToken = $this->auth->token();

        require $layoutPath;
    }
}
