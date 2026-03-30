<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class ImageOptimizer
{
    private int $webpQuality;
    private int $avifQuality;
    private bool $generateAvif;
    private bool $deleteOriginal;

    public function __construct(
        int $webpQuality = 82,
        int $avifQuality = 70,
        bool $generateAvif = false,
        bool $deleteOriginal = true
    ) {
        $this->webpQuality = max(1, min(100, $webpQuality));
        $this->avifQuality = max(1, min(100, $avifQuality));
        $this->generateAvif = $generateAvif && function_exists('imageavif');
        $this->deleteOriginal = $deleteOriginal;
    }

    /**
     * Optimize an image by converting it to WebP (and optionally AVIF).
     * Returns the path to the optimized image (WebP).
     */
    public function optimize(string $sourcePath): string
    {
        if (!file_exists($sourcePath)) {
            throw new RuntimeException('Source image does not exist: ' . $sourcePath);
        }

        $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));

        // Skip if already WebP
        if ($extension === 'webp') {
            return $sourcePath;
        }

        // Skip if already AVIF
        if ($extension === 'avif') {
            return $sourcePath;
        }

        // Load the image
        $image = $this->loadImage($sourcePath, $extension);
        if ($image === null) {
            return $sourcePath;
        }

        // Generate WebP version
        $webpPath = $this->replaceExtension($sourcePath, 'webp');
        $webpSuccess = $this->saveAsWebp($image, $webpPath);

        // Generate AVIF version if enabled and supported
        if ($this->generateAvif) {
            $avifPath = $this->replaceExtension($sourcePath, 'avif');
            $this->saveAsAvif($image, $avifPath);
        }

        // Free memory
        imagedestroy($image);

        // Delete original if configured and WebP was created successfully
        if ($webpSuccess && $this->deleteOriginal && $sourcePath !== $webpPath) {
            @unlink($sourcePath);
        }

        return $webpSuccess ? $webpPath : $sourcePath;
    }

    /**
     * Optimize and resize an image.
     */
    public function optimizeAndResize(string $sourcePath, int $maxWidth = 1920, int $maxHeight = 1080): string
    {
        if (!file_exists($sourcePath)) {
            throw new RuntimeException('Source image does not exist: ' . $sourcePath);
        }

        $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));

        // Load the image
        $image = $this->loadImage($sourcePath, $extension);
        if ($image === null) {
            return $sourcePath;
        }

        // Get current dimensions
        $width = imagesx($image);
        $height = imagesy($image);

        // Calculate new dimensions if resize is needed
        if ($width > $maxWidth || $height > $maxHeight) {
            $ratio = min($maxWidth / $width, $maxHeight / $height);
            $newWidth = (int) round($width * $ratio);
            $newHeight = (int) round($height * $ratio);

            // Create resized image
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            if ($resized === false) {
                imagedestroy($image);
                return $sourcePath;
            }

            // Preserve transparency for PNG
            if ($extension === 'png') {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
                if ($transparent !== false) {
                    imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
                }
            }

            // Resize
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $resized;
        }

        // Generate WebP version
        $webpPath = $this->replaceExtension($sourcePath, 'webp');
        $webpSuccess = $this->saveAsWebp($image, $webpPath);

        // Generate AVIF version if enabled
        if ($this->generateAvif) {
            $avifPath = $this->replaceExtension($sourcePath, 'avif');
            $this->saveAsAvif($image, $avifPath);
        }

        // Free memory
        imagedestroy($image);

        // Delete original if configured
        if ($webpSuccess && $this->deleteOriginal && $sourcePath !== $webpPath) {
            @unlink($sourcePath);
        }

        return $webpSuccess ? $webpPath : $sourcePath;
    }

    /**
     * Generate responsive images (multiple sizes) from a source image.
     * Returns array of generated file paths keyed by width.
     *
     * @param array<int, array{width: int, height: int, suffix: string}> $sizes
     * @return array<string, string> Array of ['suffix' => 'path']
     */
    public function generateResponsiveSizes(string $sourcePath, array $sizes): array
    {
        if (!file_exists($sourcePath)) {
            throw new RuntimeException('Source image does not exist: ' . $sourcePath);
        }

        $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
        $sourceExt = $extension === 'webp' ? 'webp' : $extension;

        // Load the source image
        $sourceImage = $this->loadImage($sourcePath, $sourceExt);
        if ($sourceImage === null) {
            return [];
        }

        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);
        $results = [];

        foreach ($sizes as $size) {
            $targetWidth = $size['width'];
            $targetHeight = $size['height'];
            $suffix = $size['suffix'];

            // Skip if source is smaller than target
            if ($sourceWidth <= $targetWidth && $sourceHeight <= $targetHeight) {
                continue;
            }

            // Calculate dimensions maintaining aspect ratio
            $ratio = min($targetWidth / $sourceWidth, $targetHeight / $sourceHeight);
            $newWidth = (int) round($sourceWidth * $ratio);
            $newHeight = (int) round($sourceHeight * $ratio);

            // Create resized image
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            if ($resized === false) {
                continue;
            }

            // Handle transparency
            if ($extension === 'png') {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
                if ($transparent !== false) {
                    imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
                }
            }

            // Resize
            imagecopyresampled($resized, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight);

            // Generate output path with suffix
            $info = pathinfo($sourcePath);
            $dir = $info['dirname'] ?? '';
            $filename = $info['filename'] ?? 'image';
            $outputPath = ($dir !== '' && $dir !== '.' ? $dir . '/' : '') . $filename . $suffix . '.webp';

            // Save as WebP
            if ($this->saveAsWebp($resized, $outputPath)) {
                $results[$suffix] = $outputPath;
            }

            imagedestroy($resized);
        }

        imagedestroy($sourceImage);

        return $results;
    }

    /**
     * Check if WebP support is available.
     */
    public static function isWebpSupported(): bool
    {
        return function_exists('imagewebp') && function_exists('imagecreatefromjpeg');
    }

    /**
     * Check if AVIF support is available.
     */
    public static function isAvifSupported(): bool
    {
        return function_exists('imageavif');
    }

    /**
     * Build a responsive srcset string using -sm.webp (400w), -md.webp (800w)
     * and the original image as 1800w fallback.
     */
    public static function getResponsiveSrcset(string $imagePath, string $filename = ''): string
    {
        $publicPath = self::buildPublicImagePath($imagePath, $filename);
        if ($publicPath === '') {
            return '';
        }

        $info = pathinfo($publicPath);
        $dir = $info['dirname'] ?? '';
        $baseName = $info['filename'] ?? '';
        $base = ($dir !== '' && $dir !== '.' ? $dir . '/' : '') . $baseName;

        $originalUrl = $publicPath;
        $smallUrl = $base . '-sm.webp';
        $mediumUrl = $base . '-md.webp';

        $originalExists = self::publicFileExists($originalUrl);
        $smallExists = self::publicFileExists($smallUrl);
        $mediumExists = self::publicFileExists($mediumUrl);

        $originalFallback = $originalUrl;
        if (!$originalExists) {
            if ($mediumExists) {
                $originalFallback = $mediumUrl;
            } elseif ($smallExists) {
                $originalFallback = $smallUrl;
            }
        }

        $smallFallback = $smallExists
            ? $smallUrl
            : ($mediumExists ? $mediumUrl : $originalFallback);
        $mediumFallback = $mediumExists ? $mediumUrl : $originalFallback;

        return implode(', ', [
            $smallFallback . ' 400w',
            $mediumFallback . ' 800w',
            $originalFallback . ' 1800w',
        ]);
    }

    /**
     * Load an image from file based on its extension.
     *
     * @return \GdImage|resource|null
     */
    private function loadImage(string $path, string $extension)
    {
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                if (!function_exists('imagecreatefromjpeg')) {
                    return null;
                }
                $image = @imagecreatefromjpeg($path);
                return $image !== false ? $image : null;

            case 'png':
                if (!function_exists('imagecreatefrompng')) {
                    return null;
                }
                $image = @imagecreatefrompng($path);
                if ($image !== false) {
                    imagealphablending($image, true);
                    imagesavealpha($image, true);
                }
                return $image !== false ? $image : null;

            case 'gif':
                if (!function_exists('imagecreatefromgif')) {
                    return null;
                }
                $image = @imagecreatefromgif($path);
                return $image !== false ? $image : null;

            case 'webp':
                if (!function_exists('imagecreatefromwebp')) {
                    return null;
                }
                $image = @imagecreatefromwebp($path);
                return $image !== false ? $image : null;

            default:
                return null;
        }
    }

    /**
     * Save an image as WebP.
     *
     * @param \GdImage|resource $image
     */
    private function saveAsWebp($image, string $path): bool
    {
        if (!function_exists('imagewebp')) {
            return false;
        }

        return @imagewebp($image, $path, $this->webpQuality);
    }

    /**
     * Save an image as AVIF.
     *
     * @param \GdImage|resource $image
     */
    private function saveAsAvif($image, string $path): bool
    {
        if (!function_exists('imageavif')) {
            return false;
        }

        return @imageavif($image, $path, $this->avifQuality);
    }

    /**
     * Replace the extension of a file path.
     */
    private function replaceExtension(string $path, string $newExtension): string
    {
        $info = pathinfo($path);
        $dir = $info['dirname'] ?? '';
        $filename = $info['filename'] ?? 'image';

        return ($dir !== '' && $dir !== '.' ? $dir . '/' : '') . $filename . '.' . $newExtension;
    }

    /**
     * Normalize imagePath/filename inputs to a public URL path (starting with /).
     */
    private static function buildPublicImagePath(string $imagePath, string $filename): string
    {
        $rawPath = trim($filename) !== ''
            ? rtrim($imagePath, '/') . '/' . ltrim($filename, '/')
            : $imagePath;

        if ($rawPath === '') {
            return '';
        }

        $publicRoot = self::getPublicRootPath();
        if (str_starts_with($rawPath, $publicRoot . '/')) {
            return '/' . ltrim(substr($rawPath, strlen($publicRoot)), '/');
        }

        return '/' . ltrim($rawPath, '/');
    }

    /**
     * Resolve and check a public URL path against the local public directory.
     */
    private static function publicFileExists(string $publicUrlPath): bool
    {
        $normalized = '/' . ltrim($publicUrlPath, '/');
        $absolute = self::getPublicRootPath() . $normalized;

        return is_file($absolute);
    }

    private static function getPublicRootPath(): string
    {
        if (defined('APP_ROOT')) {
            return rtrim((string) APP_ROOT, '/') . '/public';
        }

        return dirname(__DIR__, 2) . '/public';
    }
}
