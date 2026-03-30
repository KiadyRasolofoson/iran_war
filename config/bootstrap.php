<?php

declare(strict_types=1);

$rootPath = dirname(__DIR__);

/**
 * Lightweight .env loader without external dependencies.
 */
function loadEnvFile(string $filePath): void
{
    if (!is_readable($filePath)) {
        return;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $trimmed = trim($line);

        if ($trimmed === '' || str_starts_with($trimmed, '#')) {
            continue;
        }

        $parts = explode('=', $trimmed, 2);
        if (count($parts) !== 2) {
            continue;
        }

        $key = trim($parts[0]);
        $value = trim($parts[1]);

        if ($key == '') {
            continue;
        }

        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        if (getenv($key) === false) {
            putenv($key . '=' . $value);
        }

        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

loadEnvFile($rootPath . '/.env');

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    ]);
    session_start();
}

function envValue(string $key, ?string $default = null): string
{
    $value = getenv($key);
    if ($value === false || $value === '') {
        return (string) $default;
    }

    return $value;
}

define('APP_ROOT', $rootPath);
define('APP_ENV', envValue('APP_ENV', 'production'));
define('APP_URL', envValue('APP_URL', 'http://localhost'));

define('DB_HOST', envValue('DB_HOST', '127.0.0.1'));
define('DB_PORT', envValue('DB_PORT', '3306'));
define('DB_NAME', envValue('DB_NAME', 'app'));
define('DB_USER', envValue('DB_USER', 'root'));
define('DB_PASSWORD', envValue('DB_PASSWORD', ''));

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    $baseDir = APP_ROOT . '/src/';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_readable($file)) {
        require_once $file;
    }
});
