<?php

declare(strict_types=1);

$category = is_array($category ?? null) ? $category : [];
$articles = is_array($articles ?? null) ? $articles : [];
$pagination = is_array($pagination ?? null) ? $pagination : [];
$filters = is_array($filters ?? null) ? $filters : [];

$h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$categoryName = (string) ($category['name'] ?? 'Categorie');
$categorySlug = (string) ($category['slug'] ?? '');

$seoTitle = trim((string) ($category['seo_title'] ?? ''));
$pageTitle = $seoTitle !== '' ? $seoTitle : ('Articles - ' . $categoryName);

$seoDescription = trim((string) ($category['seo_description'] ?? ''));
if ($seoDescription === '') {
    $seoDescription = trim((string) ($category['description'] ?? ''));
}

$currentPage = (int) ($pagination['page'] ?? 1);
$totalPages = (int) ($pagination['total_pages'] ?? 1);
$totalArticles = (int) ($pagination['total'] ?? 0);

$q = (string) ($filters['q'] ?? '');
$date = (string) ($filters['date'] ?? '');

$buildQuery = static function (int $page) use ($q, $date): string {
    $query = [
        'page' => $page,
    ];

    if ($q !== '') {
        $query['q'] = $q;
    }

    if ($date !== '') {
        $query['date'] = $date;
    }

    return http_build_query($query);
};

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
            --bg: #f7f2ea;
            --panel: #fff;
            --text: #1f2937;
            --muted: #6b7280;
            --line: #d6d3d1;
            --accent: #5f3d26;
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Georgia, "Times New Roman", serif; color: var(--text); background: radial-gradient(circle at top right, #eee5d9, var(--bg) 55%); }
        .shell { max-width: 1040px; margin: 0 auto; padding: 24px 16px 42px; }
        .header { margin-bottom: 16px; }
        .header a { color: var(--accent); text-decoration: none; }
        h1 { margin: 6px 0 8px; font-size: clamp(1.6rem, 3.5vw, 2.35rem); }
        .desc { margin: 0; color: var(--muted); }
        .toolbar { margin-top: 16px; background: var(--panel); border: 1px solid var(--line); border-radius: 12px; padding: 12px; }
        .filters { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 10px; align-items: end; }
        label { display: block; margin-bottom: 4px; color: var(--muted); }
        input { width: 100%; border: 1px solid var(--line); border-radius: 8px; padding: 8px 10px; }
        .btn { border: 1px solid var(--accent); border-radius: 8px; background: var(--accent); color: #fff; padding: 9px 12px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn.secondary { background: #fff; color: var(--accent); }
        .meta { margin: 12px 0 16px; color: var(--muted); }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 14px; }
        .card { background: var(--panel); border: 1px solid var(--line); border-radius: 12px; overflow: hidden; display: grid; grid-template-rows: 155px auto; }
        .thumb { width: 100%; height: 100%; object-fit: cover; background: #e7e5e4; }
        .card-body { padding: 12px; }
        .card h2 { margin: 0 0 8px; font-size: 1.1rem; }
        .card h2 a { color: inherit; text-decoration: none; }
        .card h2 a:hover { text-decoration: underline; }
        .article-meta { margin: 0 0 8px; color: var(--muted); font-size: 0.9rem; }
        .empty { background: #f8e7d7; border: 1px solid #e9bf98; border-radius: 10px; padding: 14px; }
        .pagination { margin-top: 18px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    </style>
</head>
<body>
<main class="shell">
    <header class="header">
        <a href="/articles">Retour aux articles</a>
        <h1>Categorie: <?= $h($categoryName) ?></h1>
        <?php if ($seoDescription !== ''): ?>
            <p class="desc"><?= $h($seoDescription) ?></p>
        <?php endif; ?>
    </header>

    <section class="toolbar" aria-label="Filtres de categorie">
        <form class="filters" method="get" action="/categorie/<?= $h($categorySlug) ?>">
            <div>
                <label for="q">Recherche dans la categorie</label>
                <input type="text" id="q" name="q" value="<?= $h($q) ?>" placeholder="Titre ou contenu">
            </div>

            <div>
                <label for="date">Date</label>
                <input type="date" id="date" name="date" value="<?= $h($date) ?>">
            </div>

            <div>
                <button class="btn" type="submit">Filtrer</button>
                <a class="btn secondary" href="/categorie/<?= $h($categorySlug) ?>">Reinitialiser</a>
            </div>
        </form>
    </section>

    <p class="meta"><?= $h($totalArticles) ?> article(s) dans cette categorie.</p>

    <?php if ($articles === []): ?>
        <p class="empty">Aucun article publie dans cette categorie avec ces filtres.</p>
    <?php else: ?>
        <section class="grid" aria-label="Articles de categorie">
            <?php foreach ($articles as $article): ?>
                <?php
                $title = (string) ($article['title'] ?? 'Sans titre');
                $imageUrl = $resolveImageUrl($article['image'] ?? null);
                $imageAlt = trim((string) ($article['image_alt'] ?? ''));
                if ($imageAlt === '') {
                    $imageAlt = 'Illustration de l\'article ' . $title . ' pour la categorie ' . $categoryName;
                }
                $excerpt = trim((string) ($article['excerpt'] ?? ''));
                if ($excerpt === '') {
                    $excerpt = mb_substr(strip_tags((string) ($article['content'] ?? '')), 0, 150) . '...';
                }
                ?>
                <article class="card">
                    <?php if ($imageUrl !== null): ?>
                        <img class="thumb" src="<?= $h($imageUrl) ?>" alt="<?= $h($imageAlt) ?>" loading="lazy">
                    <?php else: ?>
                        <div class="thumb" role="img" aria-label="<?= $h($imageAlt) ?>"></div>
                    <?php endif; ?>
                    <div class="card-body">
                        <h2><a href="/article/<?= $h((string) ($article['slug'] ?? '')) ?>"><?= $h($title) ?></a></h2>
                        <p class="article-meta">Publie le <?= $h((string) ($article['published_at'] ?? 'Date inconnue')) ?></p>
                        <p><?= $h($excerpt) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

    <nav class="pagination" aria-label="Pagination categorie">
        <?php if (($pagination['has_prev'] ?? false) === true): ?>
            <a class="btn secondary" href="/categorie/<?= $h($categorySlug) ?>?<?= $h($buildQuery($currentPage - 1)) ?>">Page precedente</a>
        <?php endif; ?>

        <span>Page <?= $h($currentPage) ?> / <?= $h($totalPages) ?></span>

        <?php if (($pagination['has_next'] ?? false) === true): ?>
            <a class="btn secondary" href="/categorie/<?= $h($categorySlug) ?>?<?= $h($buildQuery($currentPage + 1)) ?>">Page suivante</a>
        <?php endif; ?>
    </nav>
</main>
</body>
</html>
