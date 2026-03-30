<?php

declare(strict_types=1);

use App\Core\ImageOptimizer;
require_once dirname(__DIR__) . '/config/bootstrap.php';

const MAX_WIDTH = 800;
const MAX_HEIGHT = 450;

const SUPPORTED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];
const SOURCE_EXTENSIONS_PRIORITY = ['webp', 'jpg', 'jpeg', 'png'];

const RESPONSIVE_SIZES = [
    ['width' => 400, 'height' => 250, 'suffix' => '-sm'],
];

/**
 * @return array<int, string>
 */
function collectBaseImages(string $uploadsRoot): array
{
    $baseExtensions = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($uploadsRoot, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $item) {
        if (!$item instanceof SplFileInfo || !$item->isFile()) {
            continue;
        }

        $extension = strtolower((string) $item->getExtension());
        if (!in_array($extension, SUPPORTED_EXTENSIONS, true)) {
            continue;
        }

        $filename = (string) pathinfo($item->getFilename(), PATHINFO_FILENAME);
        if (isResponsiveDerivative($filename)) {
            continue;
        }

        $basePath = $item->getPath() . '/' . $filename;
        $baseExtensions[$basePath][$extension] = true;
    }

    $sources = [];

    foreach ($baseExtensions as $basePath => $extensions) {
        $source = resolveSourcePath($basePath, array_keys($extensions));
        if ($source !== null) {
            $sources[] = $source;
        }
    }

    sort($sources);

    return $sources;
}

function isResponsiveDerivative(string $filename): bool
{
    return str_ends_with($filename, '-sm') || str_ends_with($filename, '-md');
}

/**
 * @param array<int, string> $availableExtensions
 */
function resolveSourcePath(string $basePath, array $availableExtensions): ?string
{
    foreach (SOURCE_EXTENSIONS_PRIORITY as $extension) {
        if (in_array($extension, $availableExtensions, true)) {
            return $basePath . '.' . $extension;
        }
    }

    return null;
}

function toPublicPath(string $absolutePath): string
{
    $publicRoot = rtrim(APP_ROOT, '/') . '/public';

    if (str_starts_with($absolutePath, $publicRoot . '/')) {
        return '/' . ltrim(substr($absolutePath, strlen($publicRoot)), '/');
    }

    return $absolutePath;
}

function removeStaleDerivatives(string $basePath): int
{
    $removed = 0;
    $matches = glob($basePath . '-*.webp');

    if ($matches === false) {
        return 0;
    }

    foreach ($matches as $derivativePath) {
        if (!is_file($derivativePath)) {
            continue;
        }

        if (@unlink($derivativePath)) {
            $removed++;
        }
    }

    return $removed;
}

$uploadsRoot = APP_ROOT . '/public/uploads/articles';

if (!is_dir($uploadsRoot)) {
    fwrite(STDERR, '[ERROR] Uploads directory not found: ' . $uploadsRoot . PHP_EOL);
    exit(1);
}

if (!is_readable($uploadsRoot)) {
    fwrite(STDERR, '[ERROR] Uploads directory is not readable: ' . $uploadsRoot . PHP_EOL);
    exit(1);
}

$optimizer = new ImageOptimizer(82, 70, false, true);
$sourceImages = collectBaseImages($uploadsRoot);

$total = count($sourceImages);
$okCount = 0;
$skipCount = 0;
$errorCount = 0;

foreach ($sourceImages as $sourcePath) {
    $displayPath = toPublicPath($sourcePath);

    if (!is_readable($sourcePath)) {
        echo '[ERROR] ' . $displayPath . ' (unreadable file)' . PHP_EOL;
        $errorCount++;
        continue;
    }

    $size = @getimagesize($sourcePath);
    if ($size === false || !isset($size[0], $size[1])) {
        echo '[ERROR] ' . $displayPath . ' (failed to read image dimensions)' . PHP_EOL;
        $errorCount++;
        continue;
    }

    $width = (int) $size[0];
    $height = (int) $size[1];

    if ($width <= MAX_WIDTH && $height <= MAX_HEIGHT) {
        echo '[SKIP] ' . $displayPath . ' (' . $width . 'x' . $height . ' already within bounds)' . PHP_EOL;
        $skipCount++;
        continue;
    }

    try {
        $optimizedPath = $optimizer->optimizeAndResize($sourcePath, MAX_WIDTH, MAX_HEIGHT);

        if (!is_file($optimizedPath)) {
            throw new RuntimeException('optimizer did not produce an output file');
        }

        $optimizedInfo = pathinfo($optimizedPath);
        $optimizedDir = $optimizedInfo['dirname'] ?? '';
        $optimizedFilename = $optimizedInfo['filename'] ?? '';
        $optimizedBasePath = ($optimizedDir !== '' && $optimizedDir !== '.')
            ? $optimizedDir . '/' . $optimizedFilename
            : $optimizedFilename;

        $removedDerivatives = removeStaleDerivatives($optimizedBasePath);
        $generated = $optimizer->generateResponsiveSizes($optimizedPath, RESPONSIVE_SIZES);

        $optimizedDisplay = toPublicPath($optimizedPath);
        $generatedCount = count($generated);

        echo '[OK] ' . $displayPath
            . ' -> ' . $optimizedDisplay
            . ' (resized from ' . $width . 'x' . $height
            . ', removed stale: ' . $removedDerivatives
            . ', generated: ' . $generatedCount . ')' . PHP_EOL;

        $okCount++;
    } catch (Throwable $exception) {
        echo '[ERROR] ' . $displayPath . ' (' . $exception->getMessage() . ')' . PHP_EOL;
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
