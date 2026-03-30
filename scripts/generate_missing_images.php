<?php

declare(strict_types=1);

use App\Core\ImageOptimizer;

require_once dirname(__DIR__) . '/config/bootstrap.php';

const RESPONSIVE_SIZES = [
    ['width' => 400, 'height' => 250, 'suffix' => '-sm'],
    ['width' => 800, 'height' => 450, 'suffix' => '-md'],
];

const SOURCE_EXTENSIONS_PRIORITY = ['webp', 'jpg', 'jpeg', 'png', 'gif'];
const SCAN_EXTENSIONS = ['webp', 'jpg', 'jpeg', 'png', 'gif', 'avif'];

/**
 * Build a stable list of base image paths (without extension) to process.
 * Responsive derivatives (-sm/-md) are ignored.
 *
 * @return array<int, string>
 */
function collectBaseImages(string $uploadsRoot): array
{
    $bases = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($uploadsRoot, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $item) {
        if (!$item instanceof SplFileInfo || !$item->isFile()) {
            continue;
        }

        $ext = strtolower((string) $item->getExtension());
        if (!in_array($ext, SCAN_EXTENSIONS, true)) {
            continue;
        }

        $filename = (string) pathinfo($item->getFilename(), PATHINFO_FILENAME);
        if (str_ends_with($filename, '-sm') || str_ends_with($filename, '-md')) {
            continue;
        }

        $basePath = $item->getPath() . '/' . $filename;
        $bases[$basePath] = true;
    }

    return array_keys($bases);
}

/**
 * Prefer WebP as source, then fall back to JPEG/PNG/GIF if needed.
 */
function resolveSourcePath(string $basePath): ?string
{
    foreach (SOURCE_EXTENSIONS_PRIORITY as $extension) {
        $candidate = $basePath . '.' . $extension;
        if (is_file($candidate)) {
            return $candidate;
        }
    }

    return null;
}

/**
 * @param array<int, array{width: int, height: int, suffix: string}> $sizes
 * @return array<int, array{width: int, height: int, suffix: string}>
 */
function getMissingSizes(string $basePath, array $sizes): array
{
    $missing = [];

    foreach ($sizes as $size) {
        $variantPath = $basePath . $size['suffix'] . '.webp';
        if (!is_file($variantPath)) {
            $missing[] = $size;
        }
    }

    return $missing;
}

function toPublicPath(string $absolutePath): string
{
    $publicRoot = rtrim(APP_ROOT, '/') . '/public';

    if (str_starts_with($absolutePath, $publicRoot . '/')) {
        return '/' . ltrim(substr($absolutePath, strlen($publicRoot)), '/');
    }

    return $absolutePath;
}

$uploadsRoot = APP_ROOT . '/public/uploads/articles';

if (!is_dir($uploadsRoot)) {
    fwrite(STDERR, 'Uploads directory not found: ' . $uploadsRoot . PHP_EOL);
    exit(1);
}

$optimizer = new ImageOptimizer(82, 70, false, false);
$bases = collectBaseImages($uploadsRoot);

$total = count($bases);
$okCount = 0;
$skipCount = 0;
$errorCount = 0;

foreach ($bases as $basePath) {
    $sourcePath = resolveSourcePath($basePath);
    $displayPath = toPublicPath(($sourcePath ?? $basePath));

    if ($sourcePath === null) {
        echo 'Processing ' . $displayPath . '... [SKIP] (no source file found)' . PHP_EOL;
        $skipCount++;
        continue;
    }

    $missingSizes = getMissingSizes($basePath, RESPONSIVE_SIZES);
    if ($missingSizes === []) {
        echo 'Processing ' . $displayPath . '... [SKIP] (responsive files already exist)' . PHP_EOL;
        $skipCount++;
        continue;
    }

    try {
        $generated = $optimizer->generateResponsiveSizes($sourcePath, $missingSizes);

        if ($generated === []) {
            echo 'Processing ' . $displayPath . '... [SKIP] (image too small or unsupported source format)' . PHP_EOL;
            $skipCount++;
            continue;
        }

        $generatedCount = count($generated);
        $requestedCount = count($missingSizes);

        echo 'Processing ' . $displayPath . '... [OK] (' . $generatedCount . '/' . $requestedCount . ' generated)' . PHP_EOL;
        $okCount++;
    } catch (Throwable $exception) {
        echo 'Processing ' . $displayPath . '... [ERROR] ' . $exception->getMessage() . PHP_EOL;
        $errorCount++;
    }
}

echo PHP_EOL;
echo 'Done.' . PHP_EOL;
echo 'Scanned: ' . $total . PHP_EOL;
echo 'OK: ' . $okCount . PHP_EOL;
echo 'SKIP: ' . $skipCount . PHP_EOL;
echo 'ERROR: ' . $errorCount . PHP_EOL;

exit($errorCount > 0 ? 1 : 0);
