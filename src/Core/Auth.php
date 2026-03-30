<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

final class Auth
{
    private const SESSION_USER_KEY = 'auth_user';
    private const SESSION_CSRF_KEY = 'csrf_token';

    private User $userModel;

    public function __construct(?User $userModel = null)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->userModel = $userModel ?? new User();
    }

    public function attempt(string $username, string $password): bool
    {
        $username = trim($username);
        if ($username === '' || $password === '') {
            return false;
        }

        $user = $this->userModel->findByUsername($username);
        if ($user === null) {
            return false;
        }

        $status = strtolower((string) ($user['status'] ?? ''));
        if ($status !== 'active') {
            return false;
        }

        $hash = (string) ($user['password_hash'] ?? '');
        if ($hash === '' || !password_verify($password, $hash)) {
            return false;
        }

        $this->login($user);

        return true;
    }

    public function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION[self::SESSION_USER_KEY] = $this->normalizeUser($user);
    }

    public function logout(): void
    {
        unset($_SESSION[self::SESSION_USER_KEY]);
        unset($_SESSION[self::SESSION_CSRF_KEY]);
        session_regenerate_id(true);
    }

    public function user(): ?array
    {
        $user = $_SESSION[self::SESSION_USER_KEY] ?? null;

        return is_array($user) ? $user : null;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function isAdmin(): bool
    {
        $user = $this->user();

        return $user !== null && strtolower((string) ($user['role'] ?? '')) === 'admin';
    }

    public function requireLogin(): void
    {
        if ($this->check()) {
            return;
        }

        http_response_code(302);
        header('Location: /login');
        exit;
    }

    public function requireAdmin(): void
    {
        if (!$this->check()) {
            $this->requireLogin();
        }

        if ($this->isAdmin()) {
            return;
        }

        http_response_code(403);
        header('Content-Type: text/plain; charset=UTF-8');
        echo '403 Forbidden';
        exit;
    }

    public function token(): string
    {
        $existing = $_SESSION[self::SESSION_CSRF_KEY] ?? null;
        if (is_string($existing) && $existing !== '') {
            return $existing;
        }

        $token = bin2hex(random_bytes(32));
        $_SESSION[self::SESSION_CSRF_KEY] = $token;

        return $token;
    }

    public function verifyToken(?string $token = null): bool
    {
        $submitted = $token;

        if ($submitted === null) {
            if (isset($_POST['_token']) && is_string($_POST['_token'])) {
                $submitted = $_POST['_token'];
            } elseif (isset($_SERVER['HTTP_X_CSRF_TOKEN']) && is_string($_SERVER['HTTP_X_CSRF_TOKEN'])) {
                $submitted = $_SERVER['HTTP_X_CSRF_TOKEN'];
            }
        }

        $stored = $_SESSION[self::SESSION_CSRF_KEY] ?? '';

        return is_string($submitted)
            && $submitted !== ''
            && is_string($stored)
            && $stored !== ''
            && hash_equals($stored, $submitted);
    }

    private function normalizeUser(array $user): array
    {
        return [
            'id' => (int) ($user['id'] ?? 0),
            'username' => (string) ($user['username'] ?? ''),
            'email' => (string) ($user['email'] ?? ''),
            'role' => (string) ($user['role'] ?? ''),
            'status' => (string) ($user['status'] ?? ''),
        ];
    }
}
