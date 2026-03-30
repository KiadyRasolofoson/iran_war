<?php

declare(strict_types=1);

$article = is_array($article ?? null) ? $article : [];
$relatedArticles = is_array($relatedArticles ?? null) ? $relatedArticles : [];

$h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$title = (string) ($article['title'] ?? 'Article');
$seoTitle = trim((string) ($article['meta_title'] ?? ''));
$pageTitle = $seoTitle !== '' ? $seoTitle : $title;

$seoDescription = trim((string) ($article['meta_description'] ?? ''));
if ($seoDescription === '') {
    $seoDescription = trim((string) ($article['excerpt'] ?? ''));
}
if ($seoDescription === '') {
    $seoDescription = mb_substr(strip_tags((string) ($article['content'] ?? '')), 0, 160);
}

$resolveImageUrl = static function (?string $rawPath): ?string {
    $path = trim((string) $rawPath);
    if ($path === '') {
        return null;
    }

    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }

    if (str_starts_with($path, '/')) {
        return $path;
    }

    if (str_starts_with($path, 'uploads/')) {
        return '/' . $path;
    }

    return '/uploads/articles/' . ltrim($path, '/');
};

$featuredImage = $resolveImageUrl($article['image'] ?? null);
$categoryName = trim((string) ($article['category_name'] ?? ''));
$imageAlt = trim((string) ($article['image_alt'] ?? ''));
if ($imageAlt === '') {
    $imageAlt = 'Image de couverture de l\'article ' . $title;
}
if ($categoryName !== '') {
    $imageAlt .= ' dans la categorie ' . $categoryName;
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $h($pageTitle) ?></title>
    <meta name="description" content="<?= $h($seoDescription) ?>">
    <style>
        :root {
            --bg: #f7f4ef;
            --surface: #fff;
            --text: #1f2937;
            --muted: #6b7280;
            --line: #d6d3d1;
            --accent: #7c2d12;
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Georgia, "Times New Roman", serif; color: var(--text); background: linear-gradient(180deg, #efe7db 0%, var(--bg) 280px); }
        .shell { max-width: 930px; margin: 0 auto; padding: 22px 16px 44px; }
        .top-links { margin-bottom: 14px; display: flex; gap: 10px; flex-wrap: wrap; }
        .top-links a { color: var(--accent); text-decoration: none; }
        .article { background: var(--surface); border: 1px solid var(--line); border-radius: 12px; overflow: hidden; }
        .hero { width: 100%; max-height: 420px; object-fit: cover; background: #e7e5e4; display: block; }
        .hero.fallback { height: 220px; }
        .body { padding: 18px; }
        h1 { margin: 0 0 8px; font-size: clamp(1.7rem, 3.6vw, 2.55rem); line-height: 1.15; }
        .meta { margin: 0 0 16px; color: var(--muted); }
        .meta a { color: inherit; }
        .content { line-height: 1.65; }
        .content p { margin: 0 0 12px; }
        .related { margin-top: 20px; border-top: 1px dashed var(--line); padding-top: 14px; }
        .related h2 { margin: 0 0 8px; font-size: 1.2rem; }
        .related ul { margin: 0; padding-left: 18px; }
        .related a { color: var(--accent); }
    </style>
</head>
<body>
<main class="shell">
    <nav class="top-links" aria-label="Fil d'Ariane article">
        <a href="/articles">Tous les articles</a>
        <?php if ($categoryName !== '' && (string) ($article['category_slug'] ?? '') !== ''): ?>
            <a href="/categorie/<?= $h((string) ($article['category_slug'] ?? '')) ?>">Categorie: <?= $h($categoryName) ?></a>
        <?php endif; ?>
    </nav>

    <article class="article">
        <?php if ($featuredImage !== null): ?>
            <img class="hero" src="<?= $h($featuredImage) ?>" alt="<?= $h($imageAlt) ?>">
        <?php else: ?>
            <div class="hero fallback" role="img" aria-label="<?= $h($imageAlt) ?>"></div>
        <?php endif; ?>

        <div class="body">
            <h1><?= $h($title) ?></h1>
            <p class="meta">
                Publie le <?= $h((string) ($article['published_at'] ?? 'Date inconnue')) ?>
                <?php if ($categoryName !== ''): ?>
                    | Categorie: <a href="/categorie/<?= $h((string) ($article['category_slug'] ?? '')) ?>"><?= $h($categoryName) ?></a>
                <?php endif; ?>
            </p>

            <div class="content">
                <?= (string) ($article['content'] ?? '') ?>
            </div>

            <?php if ($relatedArticles !== []): ?>
                <section class="related" aria-label="Articles lies">
                    <h2>Articles lies</h2>
                    <ul>
                        <?php foreach ($relatedArticles as $related): ?>
                            <li>
                                <a href="/article/<?= $h((string) ($related['slug'] ?? '')) ?>"><?= $h((string) ($related['title'] ?? 'Article')) ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>
        </div>
    </article>
</main>
</body>
</html>
