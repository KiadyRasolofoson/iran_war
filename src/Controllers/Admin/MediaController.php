<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Uploader;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Throwable;

final class MediaController
{
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    private Auth $auth;
    private Uploader $uploader;
    private string $publicRoot;
    private string $articlesUploadDirectory;

    public function __construct(?Auth $auth = null, ?Uploader $uploader = null)
    {
        $this->auth = $auth ?? new Auth();
        $this->uploader = $uploader ?? new Uploader();
        $this->publicRoot = rtrim(APP_ROOT . '/public', '/');
        $this->articlesUploadDirectory = $this->publicRoot . '/uploads/articles';
    }

    public function index(): void
    {
        if (!$this->auth->check()) {
            $this->respondError('Authentication required.', 401);
            return;
        }

        try {
            $images = $this->collectImages();
            $this->respondSuccess([
                'images' => $images,
                'count' => count($images),
            ]);
        } catch (Throwable $exception) {
            $this->respondError('Unable to list media files.', 500, [
                'details' => $exception->getMessage(),
            ]);
        }
    }

    public function upload(): void
    {
        if (!$this->auth->check()) {
            $this->respondError('Authentication required.', 401);
            return;
        }

        if (!$this->auth->verifyToken()) {
            $this->respondError('Invalid CSRF token.', 403);
            return;
        }

        $file = $this->resolveUploadedFile();
        if ($file === null) {
            $this->respondError('Missing uploaded file. Use field name image or file.', 400);
            return;
        }

        try {
            $relativePath = $this->uploader->upload($file, 'articles');
            $publicUrl = '/' . ltrim(str_replace('\\', '/', $relativePath), '/');

            $this->respondSuccess([
                'path' => $relativePath,
                'url' => $publicUrl,
            ], 201);
        } catch (Throwable $exception) {
            $this->respondError('Image upload failed.', 422, [
                'details' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function collectImages(): array
    {
        if (!is_dir($this->articlesUploadDirectory)) {
            return [];
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->articlesUploadDirectory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        $images = [];
        foreach ($iterator as $item) {
            if (!$item->isFile()) {
                continue;
            }

            $realPath = $item->getRealPath();
            if (!is_string($realPath) || $realPath === '') {
                continue;
            }

            $extension = strtolower((string) pathinfo($realPath, PATHINFO_EXTENSION));
            if (!in_array($extension, self::ALLOWED_EXTENSIONS, true)) {
                continue;
            }

            $relativePath = $this->toPublicRelativePath($realPath);
            if ($relativePath === null) {
                continue;
            }

            $images[] = [
                'name' => $item->getFilename(),
                'path' => $relativePath,
                'url' => '/' . ltrim($relativePath, '/'),
                'size' => $item->getSize(),
                'lastModified' => date(DATE_ATOM, $item->getMTime()),
            ];
        }

        usort(
            $images,
            static fn (array $left, array $right): int => strcmp((string) $right['lastModified'], (string) $left['lastModified'])
        );

        return $images;
    }

    private function toPublicRelativePath(string $realPath): ?string
    {
        $normalizedRealPath = str_replace('\\', '/', $realPath);
        $normalizedPublicRoot = str_replace('\\', '/', $this->publicRoot) . '/';

        if (strpos($normalizedRealPath, $normalizedPublicRoot) !== 0) {
            return null;
        }

        return ltrim(substr($normalizedRealPath, strlen($normalizedPublicRoot)), '/');
    }

    private function resolveUploadedFile(): ?array
    {
        if (isset($_FILES['image']) && is_array($_FILES['image'])) {
            return $_FILES['image'];
        }

        if (isset($_FILES['file']) && is_array($_FILES['file'])) {
            return $_FILES['file'];
        }

        return null;
    }

    private function respondSuccess(array $data, int $statusCode = 200): void
    {
        $this->respond([
            'success' => true,
            'data' => $data,
        ], $statusCode);
    }

    private function respondError(string $message, int $statusCode, array $extra = []): void
    {
        $this->respond(array_merge([
            'success' => false,
            'error' => [
                'message' => $message,
            ],
        ], $extra), $statusCode);
    }

    private function respond(array $payload, int $statusCode): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');

        $json = json_encode($payload, JSON_UNESCAPED_SLASHES);
        if (!is_string($json)) {
            throw new RuntimeException('Unable to encode JSON response.');
        }

        echo $json;
    }
}
