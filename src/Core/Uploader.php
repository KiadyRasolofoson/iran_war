<?php

declare(strict_types=1);

namespace App\Core;

use InvalidArgumentException;
use RuntimeException;

final class Uploader
{
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    /**
     * @var array<string, array<int, string>>
     */
    private const EXTENSION_MIME_MAP = [
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'webp' => ['image/webp'],
        'gif' => ['image/gif'],
    ];

    private const BLOCKED_EXTENSIONS = [
        'php', 'phtml', 'php3', 'php4', 'php5', 'phar',
        'exe', 'sh', 'bash', 'cgi', 'pl', 'py', 'js', 'jar',
    ];

    private string $uploadRoot;
    private string $publicBase;
    private int $maxBytes;

    public function __construct(?string $uploadRoot = null, ?string $publicBase = null, int $maxBytes = 5242880)
    {
        $this->uploadRoot = rtrim($uploadRoot ?? (APP_ROOT . '/public/uploads'), '/');
        $this->publicBase = trim($publicBase ?? 'uploads', '/');
        $this->maxBytes = max(1, $maxBytes);
    }

    public function upload(array $file, string $subDirectory = 'articles'): string
    {
        $this->validateUploadArray($file);

        $tmpName = (string) $file['tmp_name'];
        if (!is_uploaded_file($tmpName)) {
            throw new RuntimeException('Invalid uploaded file source.');
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > $this->maxBytes) {
            throw new RuntimeException('Uploaded file size is invalid or exceeds max size.');
        }

        $originalName = (string) ($file['name'] ?? '');
        $extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
            throw new RuntimeException('File extension is not allowed.');
        }

        if (in_array($extension, self::BLOCKED_EXTENSIONS, true)) {
            throw new RuntimeException('Executable file extensions are forbidden.');
        }

        $mime = $this->detectMimeType($tmpName);
        $allowedMimes = self::EXTENSION_MIME_MAP[$extension] ?? [];
        if ($allowedMimes === [] || !in_array($mime, $allowedMimes, true)) {
            throw new RuntimeException('Detected mime type is not allowed for this extension.');
        }

        $this->assertSafeBinaryContent($tmpName);

        $safeDir = $this->normalizeSubDirectory($subDirectory);
        $targetDir = $this->uploadRoot . '/' . $safeDir;

        if (!is_dir($targetDir)) {
            $parentDir = $this->findNearestExistingParentDirectory($targetDir);
            if (!is_writable($parentDir)) {
                throw new RuntimeException('Upload parent directory is not writable: ' . $parentDir);
            }

            $mkdirWarning = null;
            if (!$this->createDirectorySilently($targetDir, 0755, true, $mkdirWarning) && !is_dir($targetDir)) {
                $message = 'Unable to create upload directory: ' . $targetDir;
                if (is_string($mkdirWarning) && $mkdirWarning !== '') {
                    $message .= '. Details: ' . $mkdirWarning;
                }

                throw new RuntimeException($message);
            }
        }

        if (!is_writable($targetDir)) {
            throw new RuntimeException('Upload directory is not writable: ' . $targetDir);
        }

        $baseName = (string) pathinfo($originalName, PATHINFO_FILENAME);
        $slug = $this->slugify($baseName);
        $uniqueName = $slug . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(6)) . '.' . $extension;

        $destination = $targetDir . '/' . $uniqueName;
        $moveWarning = null;
        if (!$this->moveUploadedFileSilently($tmpName, $destination, $moveWarning)) {
            $message = 'Unable to move uploaded file to destination: ' . $destination;
            if (is_string($moveWarning) && $moveWarning !== '') {
                $message .= '. Details: ' . $moveWarning;
            }

            throw new RuntimeException($message);
        }

        return $this->publicBase . '/' . $safeDir . '/' . $uniqueName;
    }

    private function validateUploadArray(array $file): void
    {
        if (!isset($file['error']) || !is_int($file['error'])) {
            throw new InvalidArgumentException('Invalid upload payload.');
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Upload failed with error code: ' . $file['error']);
        }

        if (!isset($file['tmp_name']) || !is_string($file['tmp_name']) || $file['tmp_name'] === '') {
            throw new InvalidArgumentException('Missing temporary uploaded file.');
        }
    }

    private function detectMimeType(string $filePath): string
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($filePath);

        if (!is_string($mime) || $mime === '') {
            throw new RuntimeException('Unable to detect uploaded mime type.');
        }

        return strtolower(trim($mime));
    }

    private function assertSafeBinaryContent(string $filePath): void
    {
        $sample = file_get_contents($filePath, false, null, 0, 1024);
        if (!is_string($sample) || $sample === '') {
            throw new RuntimeException('Unable to inspect uploaded file content.');
        }

        $patterns = [
            '/<\?(php|=)?/i',
            '/<script\b/i',
            '/#!\//',
            '/MZ/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $sample) === 1) {
                throw new RuntimeException('Uploaded file contains forbidden executable markers.');
            }
        }
    }

    private function normalizeSubDirectory(string $subDirectory): string
    {
        $subDirectory = trim(str_replace('\\', '/', $subDirectory), '/');
        if ($subDirectory === '') {
            return 'articles';
        }

        $segments = explode('/', $subDirectory);
        $safeSegments = [];

        foreach ($segments as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                continue;
            }

            $safeSegment = $this->slugify($segment);
            if ($safeSegment !== '') {
                $safeSegments[] = $safeSegment;
            }
        }

        if ($safeSegments === []) {
            return 'articles';
        }

        return implode('/', $safeSegments);
    }

    private function findNearestExistingParentDirectory(string $path): string
    {
        $candidate = rtrim($path, '/');

        while ($candidate !== '' && !is_dir($candidate)) {
            $next = dirname($candidate);
            if ($next === $candidate) {
                break;
            }

            $candidate = $next;
        }

        if ($candidate === '' || !is_dir($candidate)) {
            throw new RuntimeException('No existing parent directory found for upload path: ' . $path);
        }

        return $candidate;
    }

    private function createDirectorySilently(string $directory, int $permissions, bool $recursive, ?string &$warning = null): bool
    {
        $warning = null;
        set_error_handler(static function (int $severity, string $message) use (&$warning): bool {
            $warning = $message;
            return true;
        });

        try {
            return mkdir($directory, $permissions, $recursive);
        } finally {
            restore_error_handler();
        }
    }

    private function moveUploadedFileSilently(string $from, string $to, ?string &$warning = null): bool
    {
        $warning = null;
        set_error_handler(static function (int $severity, string $message) use (&$warning): bool {
            $warning = $message;
            return true;
        });

        try {
            return move_uploaded_file($from, $to);
        } finally {
            restore_error_handler();
        }
    }

    private function slugify(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return 'file';
        }

        $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($transliterated !== false) {
            $value = $transliterated;
        }

        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        $value = trim($value, '-');

        return $value !== '' ? $value : 'file';
    }
}
