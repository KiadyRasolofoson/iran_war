<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private static ?self $instance = null;

    private PDO $connection;

    private function __construct()
    {
        $this->connection = $this->createConnection();
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    private function createConnection(): PDO
    {
        $host = $this->configValue('DB_HOST', defined('DB_HOST') ? DB_HOST : '127.0.0.1');
        $port = $this->configValue('DB_PORT', defined('DB_PORT') ? DB_PORT : '3306');
        $name = $this->configValue('DB_NAME', defined('DB_NAME') ? DB_NAME : 'app');
        $user = $this->configValue('DB_USER', defined('DB_USER') ? DB_USER : 'root');
        $password = $this->configValue('DB_PASSWORD', defined('DB_PASSWORD') ? DB_PASSWORD : '');

        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $name);

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci',
        ];

        try {
            return new PDO($dsn, $user, $password, $options);
        } catch (PDOException $exception) {
            throw new RuntimeException(
                'Database connection failed: ' . $exception->getMessage(),
                (int) $exception->getCode(),
                $exception
            );
        }
    }

    private function configValue(string $key, string $default): string
    {
        $value = getenv($key);

        if ($value === false || $value === '') {
            return $default;
        }

        return $value;
    }
}
