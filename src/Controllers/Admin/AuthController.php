<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;

final class AuthController
{
    private Auth $auth;

    public function __construct(?Auth $auth = null)
    {
        $this->auth = $auth ?? new Auth();
    }

    public function showLogin(): void
    {
        if ($this->auth->check()) {
            $this->redirect('/admin/dashboard');
        }

        $error = '';
        if (isset($_SESSION['auth_error']) && is_string($_SESSION['auth_error'])) {
            $error = $_SESSION['auth_error'];
        }
        unset($_SESSION['auth_error']);

        $oldUsername = '';
        if (isset($_SESSION['auth_old_username']) && is_string($_SESSION['auth_old_username'])) {
            $oldUsername = $_SESSION['auth_old_username'];
        }
        unset($_SESSION['auth_old_username']);

        $csrfToken = $this->auth->token();

        $this->render(
            'admin/login',
            [
                'error' => $error,
                'oldUsername' => $oldUsername,
                'csrfToken' => $csrfToken,
            ],
            'Connexion admin',
            false
        );
    }

    public function login(): void
    {
        if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            header('Content-Type: text/plain; charset=UTF-8');
            echo '405 Method Not Allowed';
            return;
        }

        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        $_SESSION['auth_old_username'] = $username;

        if (!$this->auth->verifyToken(isset($_POST['_token']) ? (string) $_POST['_token'] : null)) {
            $_SESSION['auth_error'] = 'Session expiree ou formulaire invalide. Veuillez reessayer.';
            $this->redirect('/login');
        }

        if ($username === '' || $password === '') {
            $_SESSION['auth_error'] = 'Le nom d\'utilisateur et le mot de passe sont obligatoires.';
            $this->redirect('/login');
        }

        if (!$this->auth->attempt($username, $password)) {
            $_SESSION['auth_error'] = 'Identifiants invalides ou compte inactif.';
            $this->redirect('/login');
        }

        unset($_SESSION['auth_old_username']);

        $this->redirect('/admin/dashboard');
    }

    public function logout(): void
    {
        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

        if ($method === 'POST') {
            $token = isset($_POST['_token']) ? (string) $_POST['_token'] : null;
            if (!$this->auth->verifyToken($token)) {
                http_response_code(419);
                header('Content-Type: text/plain; charset=UTF-8');
                echo '419 CSRF Token Mismatch';
                return;
            }
        }

        $this->auth->logout();
        $this->redirect('/login');
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

    private function redirect(string $to): void
    {
        http_response_code(302);
        header('Location: ' . $to);
        exit;
    }
}
