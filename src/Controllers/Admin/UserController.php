<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Models\User;

final class UserController
{
    private User $userModel;
    private Auth $auth;

    public function __construct(?User $userModel = null, ?Auth $auth = null)
    {
        $this->userModel = $userModel ?? new User();
        $this->auth = $auth ?? new Auth();
    }

    public function index(): void
    {
        $this->auth->requireLogin();

        $users = $this->userModel->list(500, 0);

        $this->render('admin/users/index', [
            'users' => $users,
            'flashSuccess' => $this->pullFlash('success'),
            'flashError' => $this->pullFlash('error'),
            'csrfToken' => $this->auth->token(),
            'currentUser' => $this->auth->user(),
        ], 'Gestion des utilisateurs');
    }

    public function create(): void
    {
        $this->auth->requireLogin();

        $this->render('admin/users/create', [
            'errors' => [],
            'old' => [
                'username' => '',
                'email' => '',
                'role' => 'editor',
            ],
            'csrfToken' => $this->auth->token(),
            'currentUser' => $this->auth->user(),
        ], 'Creer un utilisateur');
    }

    public function store(): void
    {
        $this->auth->requireLogin();
        $this->requirePostAndCsrf();

        $password = (string) ($_POST['password'] ?? '');
        $payload = $this->userPayloadFromRequest();
        $errors = $this->validateUserPayload($payload, $password, true);

        $existing = $this->userModel->findByUsername((string) $payload['username']);
        if ($existing !== null) {
            $errors[] = 'Le nom utilisateur est deja utilise.';
        }

        if ((string) $payload['role'] === 'admin') {
            $this->auth->requireAdmin();
        }

        if ($errors !== []) {
            http_response_code(422);
            $this->render('admin/users/create', [
                'errors' => $errors,
                'old' => $payload,
                'csrfToken' => $this->auth->token(),
                'currentUser' => $this->auth->user(),
            ], 'Creer un utilisateur');

            return;
        }

        $payload['password'] = password_hash($password, PASSWORD_DEFAULT);

        try {
            $this->userModel->create($payload);
        } catch (\Throwable $throwable) {
            http_response_code(422);
            $this->render('admin/users/create', [
                'errors' => ['Impossible de creer cet utilisateur (username/email probablement deja pris).'],
                'old' => $payload,
                'csrfToken' => $this->auth->token(),
                'currentUser' => $this->auth->user(),
            ], 'Creer un utilisateur');

            return;
        }

        $this->flash('success', 'Utilisateur cree avec succes.');
        $this->redirect('/admin/users');
    }

    public function edit(string $id): void
    {
        $this->auth->requireLogin();

        $userId = (int) $id;
        $user = $this->userModel->findById($userId);

        if ($user === null) {
            $this->notFound('Utilisateur introuvable.');
        }

        $this->render('admin/users/edit', [
            'errors' => [],
            'user' => $user,
            'csrfToken' => $this->auth->token(),
            'currentUser' => $this->auth->user(),
        ], 'Modifier un utilisateur');
    }

    public function update(string $id): void
    {
        $this->auth->requireLogin();
        $this->requirePostAndCsrf();

        $userId = (int) $id;
        $existingUser = $this->userModel->findById($userId);

        if ($existingUser === null) {
            $this->notFound('Utilisateur introuvable.');
        }

        $password = (string) ($_POST['password'] ?? '');
        $payload = $this->userPayloadFromRequest();
        $errors = $this->validateUserPayload($payload, $password, false);

        $sameUsername = $this->userModel->findByUsername((string) $payload['username']);
        if ($sameUsername !== null && (int) $sameUsername['id'] !== $userId) {
            $errors[] = 'Le nom utilisateur est deja utilise.';
        }

        if ((string) $payload['role'] !== (string) $existingUser['role']) {
            $this->auth->requireAdmin();
        }

        if ($errors !== []) {
            http_response_code(422);
            $this->render('admin/users/edit', [
                'errors' => $errors,
                'user' => array_merge($existingUser, $payload),
                'csrfToken' => $this->auth->token(),
                'currentUser' => $this->auth->user(),
            ], 'Modifier un utilisateur');

            return;
        }

        $updateData = [
            'username' => $payload['username'],
            'email' => $payload['email'],
            'role' => $payload['role'],
        ];

        if (trim($password) !== '') {
            $updateData['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        try {
            $this->userModel->update($userId, $updateData);
        } catch (\Throwable $throwable) {
            http_response_code(422);
            $this->render('admin/users/edit', [
                'errors' => ['Impossible de mettre a jour cet utilisateur (username/email probablement deja pris).'],
                'user' => array_merge($existingUser, $payload),
                'csrfToken' => $this->auth->token(),
                'currentUser' => $this->auth->user(),
            ], 'Modifier un utilisateur');

            return;
        }

        $this->flash('success', 'Utilisateur mis a jour.');
        $this->redirect('/admin/users');
    }

    public function delete(string $id): void
    {
        $this->auth->requireAdmin();
        $this->requirePostAndCsrf();

        $userId = (int) $id;
        $currentUser = $this->auth->user();

        if ($currentUser !== null && (int) ($currentUser['id'] ?? 0) === $userId) {
            $this->flash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            $this->redirect('/admin/users');
        }

        $this->userModel->delete($userId);
        $this->flash('success', 'Utilisateur supprime.');
        $this->redirect('/admin/users');
    }

    private function userPayloadFromRequest(): array
    {
        $role = trim((string) ($_POST['role'] ?? 'editor'));

        // Non-admin users are restricted to editor role when creating/updating others.
        if (!$this->auth->isAdmin() && $role !== 'admin') {
            $role = 'editor';
        }

        return [
            'username' => trim((string) ($_POST['username'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'role' => $role,
        ];
    }

    private function validateUserPayload(array $payload, string $password, bool $isCreate): array
    {
        $errors = [];

        $username = (string) $payload['username'];
        if ($username === '') {
            $errors[] = 'Le nom utilisateur est obligatoire.';
        } elseif (!preg_match('/^[a-zA-Z0-9_.-]{3,50}$/', $username)) {
            $errors[] = 'Le nom utilisateur doit faire 3 a 50 caracteres (lettres, chiffres, _.-).';
        }

        $email = (string) $payload['email'];
        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'L\'email est invalide.';
        }

        $allowedRoles = ['admin', 'editor'];
        if (!in_array((string) $payload['role'], $allowedRoles, true)) {
            $errors[] = 'Le role doit etre admin ou editor.';
        }

        if ($isCreate && trim($password) === '') {
            $errors[] = 'Le mot de passe est obligatoire.';
        }

        if (trim($password) !== '' && strlen($password) < 8) {
            $errors[] = 'Le mot de passe doit contenir au moins 8 caracteres.';
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
}
