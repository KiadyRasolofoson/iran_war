<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/bootstrap.php';

use App\Controllers\Admin\ArticleController as AdminArticleController;
use App\Controllers\Admin\AuthController as AdminAuthController;
use App\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\UserController as AdminUserController;
use App\Controllers\Front\ArticleController as FrontArticleController;
use App\Controllers\Front\HomeController;
use App\Controllers\Front\PageController;
use App\Core\Router;

$router = new Router();

// FrontOffice
$router->get('/', [HomeController::class, 'index']);
$router->get('/articles', [FrontArticleController::class, 'index']);
$router->get('/article/{slug}', [FrontArticleController::class, 'show']);
$router->get('/categorie/{slug}', [FrontArticleController::class, 'byCategory']);
$router->get('/a-propos', [PageController::class, 'about']);

// BackOffice auth and dashboard
$router->get('/login', [AdminAuthController::class, 'showLogin']);
$router->get('/login/', [AdminAuthController::class, 'showLogin']);
$router->post('/login', [AdminAuthController::class, 'login']);
$router->post('/login/', [AdminAuthController::class, 'login']);
$router->post('/logout', [AdminAuthController::class, 'logout']);
$router->get('/admin/dashboard', [DashboardController::class, 'index']);

// BackOffice articles
$router->get('/admin/articles', [AdminArticleController::class, 'index']);
$router->get('/admin/articles/create', [AdminArticleController::class, 'create']);
$router->post('/admin/articles', [AdminArticleController::class, 'store']);
$router->get('/admin/articles/{id}/edit', [AdminArticleController::class, 'edit']);
$router->post('/admin/articles/{id}/update', [AdminArticleController::class, 'update']);
$router->post('/admin/articles/{id}/delete', [AdminArticleController::class, 'delete']);
$router->post('/admin/articles/{id}/toggle-status', [AdminArticleController::class, 'toggleStatus']);

// BackOffice categories
$router->get('/admin/categories', [AdminCategoryController::class, 'index']);
$router->get('/admin/categories/create', [AdminCategoryController::class, 'create']);
$router->post('/admin/categories', [AdminCategoryController::class, 'store']);
$router->get('/admin/categories/{id}/edit', [AdminCategoryController::class, 'edit']);
$router->post('/admin/categories/{id}/update', [AdminCategoryController::class, 'update']);
$router->post('/admin/categories/{id}/delete', [AdminCategoryController::class, 'delete']);

// BackOffice users
$router->get('/admin/users', [AdminUserController::class, 'index']);
$router->get('/admin/users/create', [AdminUserController::class, 'create']);
$router->post('/admin/users', [AdminUserController::class, 'store']);
$router->get('/admin/users/{id}/edit', [AdminUserController::class, 'edit']);
$router->post('/admin/users/{id}/update', [AdminUserController::class, 'update']);
$router->post('/admin/users/{id}/delete', [AdminUserController::class, 'delete']);

$router->setNotFoundHandler(static function (string $method, string $path): void {
    $safePath = htmlspecialchars($path, ENT_QUOTES, 'UTF-8');
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!doctype html>';
    echo '<html lang="fr">';
    echo '<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>404 - Page introuvable</title></head>';
    echo '<body style="font-family: sans-serif; margin: 2rem;">';
    echo '<h1>404 - Page introuvable</h1>';
    echo '<p>Aucune route ne correspond a <code>' . htmlspecialchars($method, ENT_QUOTES, 'UTF-8') . ' ' . $safePath . '</code>.</p>';
    echo '<p><a href="/">Retour a l\'accueil</a></p>';
    echo '</body></html>';
});

$router->dispatch();
