<?php

declare(strict_types=1);

use App\Core\Database;

require_once dirname(__DIR__) . '/config/bootstrap.php';

/**
 * Normalize local absolute /uploads URLs and flatten nested template columns.
 */
function cleanupHtmlContent(string $html, array $localHosts): array
{
    [$urlNormalizedHtml, $urlNormalizedCount] = normalizeLocalUploadsUrls($html, $localHosts);
    [$flattenedHtml, $flattenedCount] = flattenNestedTemplateColumns($urlNormalizedHtml);

    return [$flattenedHtml, $urlNormalizedCount, $flattenedCount];
}

/**
 * Convert absolute local URLs that point to /uploads/* into relative /uploads/* paths.
 */
function normalizeLocalUploadsUrls(string $html, array $localHosts): array
{
    $count = 0;

    $pattern = '~https?://(?P<host>[^\s/"\'\'<>:]+)(?::\d+)?(?P<path>/uploads/[^\s"\'\'<>?#]*)(?P<suffix>(?:\?[^\s"\'\'<>#]*)?(?:#[^\s"\'\'<>]*)?)~i';

    $updatedHtml = preg_replace_callback(
        $pattern,
        static function (array $matches) use ($localHosts, &$count): string {
            $host = strtolower($matches['host']);

            if (!in_array($host, $localHosts, true)) {
                return $matches[0];
            }

            $count++;

            return $matches['path'] . ($matches['suffix'] ?? '');
        },
        $html
    );

    if ($updatedHtml === null) {
        return [$html, 0];
    }

    return [$updatedHtml, $count];
}

/**
 * Unwrap only .editor-template-columns nodes that are descendants of another
 * .editor-template-columns node.
 */
function flattenNestedTemplateColumns(string $html): array
{
    if (substr_count($html, 'editor-template-columns') < 2) {
        return [$html, 0];
    }

    $dom = new \DOMDocument('1.0', 'UTF-8');
    $dom->preserveWhiteSpace = true;
    $dom->formatOutput = false;

    $wrappedHtml = '<div id="__cleanup_root__">' . $html . '</div>';

    $internalErrors = libxml_use_internal_errors(true);
    $loaded = $dom->loadHTML(
        '<?xml encoding="utf-8" ?>' . $wrappedHtml,
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );
    libxml_clear_errors();
    libxml_use_internal_errors($internalErrors);

    if ($loaded === false) {
        return [$html, 0];
    }

    $xpath = new \DOMXPath($dom);
    $nestedQuery = "//*[contains(concat(' ', normalize-space(@class), ' '), ' editor-template-columns ')][ancestor::*[contains(concat(' ', normalize-space(@class), ' '), ' editor-template-columns ')]]";
    $nestedNodes = $xpath->query($nestedQuery);

    if ($nestedNodes === false || $nestedNodes->length === 0) {
        return [$html, 0];
    }

    $nodesToFlatten = [];
    foreach ($nestedNodes as $node) {
        if ($node instanceof \DOMElement) {
            $nodesToFlatten[] = $node;
        }
    }

    $flattenedCount = 0;

    foreach ($nodesToFlatten as $node) {
        $parent = $node->parentNode;

        if ($parent === null) {
            continue;
        }

        while ($node->firstChild !== null) {
            $parent->insertBefore($node->firstChild, $node);
        }

        $parent->removeChild($node);
        $flattenedCount++;
    }

    if ($flattenedCount === 0) {
        return [$html, 0];
    }

    $root = $dom->getElementById('__cleanup_root__');
    if (!$root instanceof \DOMElement) {
        return [$html, 0];
    }

    $resultHtml = '';
    foreach ($root->childNodes as $childNode) {
        $resultHtml .= $dom->saveHTML($childNode);
    }

    return [$resultHtml, $flattenedCount];
}

/**
 * Build local host list from fixed hosts + APP_URL + machine host name.
 */
function buildLocalHosts(): array
{
    $hosts = ['localhost', '127.0.0.1'];

    $appUrlHost = parse_url((string) APP_URL, PHP_URL_HOST);
    if (is_string($appUrlHost) && $appUrlHost !== '') {
        $hosts[] = strtolower($appUrlHost);
    }

    $machineHost = gethostname();
    if (is_string($machineHost) && $machineHost !== '') {
        $hosts[] = strtolower($machineHost);
    }

    return array_values(array_unique($hosts));
}

try {
    $db = Database::getInstance()->getConnection();
    $localHosts = buildLocalHosts();

    $selectStatement = $db->query('SELECT id, content FROM articles ORDER BY id ASC');
    $updateStatement = $db->prepare('UPDATE articles SET content = :content WHERE id = :id');

    $scannedRows = 0;
    $changedRows = 0;
    $urlNormalizedCount = 0;
    $nestedColumnsFlattenedCount = 0;
    $changedIds = [];

    $db->beginTransaction();

    while (($row = $selectStatement->fetch()) !== false) {
        $scannedRows++;

        $id = (int) ($row['id'] ?? 0);
        $originalContent = (string) ($row['content'] ?? '');

        [$cleanedContent, $normalizedForRow, $flattenedForRow] = cleanupHtmlContent($originalContent, $localHosts);

        $urlNormalizedCount += $normalizedForRow;
        $nestedColumnsFlattenedCount += $flattenedForRow;

        if ($cleanedContent === $originalContent) {
            continue;
        }

        $updateStatement->bindValue(':id', $id, \PDO::PARAM_INT);
        $updateStatement->bindValue(':content', $cleanedContent, \PDO::PARAM_STR);
        $updateStatement->execute();

        $changedRows++;
        $changedIds[] = $id;
    }

    $db->commit();

    echo 'Scanned rows: ' . $scannedRows . PHP_EOL;
    echo 'Changed rows: ' . $changedRows . PHP_EOL;
    echo 'URL-normalized count: ' . $urlNormalizedCount . PHP_EOL;
    echo 'Nested-columns-flattened count: ' . $nestedColumnsFlattenedCount . PHP_EOL;
    echo 'Changed IDs: ' . ($changedIds === [] ? '(none)' : implode(', ', $changedIds)) . PHP_EOL;
} catch (\Throwable $exception) {
    if (isset($db) && $db instanceof \PDO && $db->inTransaction()) {
        $db->rollBack();
    }

    fwrite(STDERR, 'Cleanup failed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
