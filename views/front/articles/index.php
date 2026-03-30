<?php

declare(strict_types=1);

$articles = is_array($articles ?? null) ? $articles : [];
$pagination = is_array($pagination ?? null) ? $pagination : [];
$categories = is_array($categories ?? null) ? $categories : [];
$filters = is_array($filters ?? null) ? $filters : [];
$selectedCategory = is_array($selectedCategory ?? null) ? $selectedCategory : null;

$h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$currentPage = (int) ($pagination['page'] ?? 1);
$totalPages = (int) ($pagination['total_pages'] ?? 1);
$totalArticles = (int) ($pagination['total'] ?? 0);

$q = (string) ($filters['q'] ?? '');
$category = (string) ($filters['category'] ?? '');
$date = (string) ($filters['date'] ?? '');

$pageTitle = 'Articles';
if ($selectedCategory !== null) {
    $pageTitle = 'Articles - ' . (string) ($selectedCategory['name'] ?? 'Categorie');
}
if ($q !== '') {
    $pageTitle .= ' - Recherche';
}

$buildQuery = static function (int $page) use ($q, $category, $date): string {
    $query = [
        'page' => $page,
    ];

    if ($q !== '') {
        $query['q'] = $q;
    }

    if ($category !== '') {
        $query['category'] = $category;
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
    <meta name="description" content="Parcourez les articles publies avec recherche, filtres et navigation par categorie.">
    <style>
        :root {
            --bg: #f8f7f3;
            --panel: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --line: #d6d3d1;
            --accent: #8b4513;
            --accent-soft: #fce7d5;
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Georgia, "Times New Roman", serif; color: var(--text); background: radial-gradient(circle at top left, #efe8df, var(--bg) 55%); }
        .shell { max-width: 1100px; margin: 0 auto; padding: 24px 16px 48px; }
        h1 { margin: 0 0 10px; font-size: clamp(1.6rem, 3.5vw, 2.4rem); line-height: 1.15; }
        .lead { margin: 0 0 18px; color: var(--muted); }
        .toolbar { background: var(--panel); border: 1px solid var(--line); border-radius: 12px; padding: 14px; margin-bottom: 20px; }
        .filters { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 10px; align-items: end; }
        label { display: block; margin-bottom: 4px; font-size: 0.92rem; color: var(--muted); }
        input, select { width: 100%; border: 1px solid var(--line); border-radius: 8px; padding: 8px 10px; background: #fff; color: var(--text); }
        .btn { border: 1px solid var(--accent); border-radius: 8px; background: var(--accent); color: #fff; padding: 9px 12px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn.secondary { background: #fff; color: var(--accent); }
        .list-meta { margin: 8px 0 18px; color: var(--muted); }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(255px, 1fr)); gap: 14px; }
        .card { background: var(--panel); border: 1px solid var(--line); border-radius: 12px; overflow: hidden; display: grid; grid-template-rows: 160px auto; }
        .thumb { width: 100%; height: 100%; object-fit: cover; background: #e7e5e4; }
        .card-body { padding: 12px; }
        .card h2 { margin: 0 0 8px; font-size: 1.15rem; }
        .card h2 a { color: inherit; text-decoration: none; }
        .card h2 a:hover { text-decoration: underline; }
        .meta { margin: 0 0 8px; font-size: 0.9rem; color: var(--muted); }
        .excerpt { margin: 0; line-height: 1.45; }
        .empty { background: var(--accent-soft); border: 1px solid #f1c9a5; border-radius: 10px; padding: 14px; }
        .pagination { margin-top: 20px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .pagination span { color: var(--muted); }
        @media (max-width: 640px) {
            .card { grid-template-rows: 140px auto; }
        }
    </style>
</head>
<body>
<main class="shell">
    <h1>Articles publies</h1>
    <p class="lead">Explorez les analyses par mots-cles, categorie et date de publication.</p>

    <section class="toolbar" aria-label="Filtres de recherche">
        <form class="filters" method="get" action="/articles">
            <div>
                <label for="q">Recherche</label>
                <input type="text" id="q" name="q" value="<?= $h($q) ?>" placeholder="Titre ou contenu">
            </div>

            <div>
                <label for="category">Categorie</label>
                <select id="category" name="category">
                    <option value="">Toutes les categories</option>
                    <?php foreach ($categories as $item): ?>
                        <?php $slug = (string) ($item['slug'] ?? ''); ?>
                        <option value="<?= $h($slug) ?>" <?= $slug === $category ? 'selected' : '' ?>>
                            <?= $h((string) ($item['name'] ?? '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="date">Date</label>
                <input type="date" id="date" name="date" value="<?= $h($date) ?>">
            </div>

            <div>
                <button class="btn" type="submit">Filtrer</button>
                <a class="btn secondary" href="/articles">Reinitialiser</a>
            </div>
        </form>
    </section>

    <p class="list-meta"><?= $h($totalArticles) ?> article(s) trouves.</p>

    <?php if ($articles === []): ?>
        <p class="empty">Aucun article publie ne correspond a ces filtres.</p>
    <?php else: ?>
        <section class="grid" aria-label="Liste des articles">
            <?php foreach ($articles as $article): ?>
                <?php
                $title = (string) ($article['title'] ?? 'Sans titre');
                $excerpt = trim((string) ($article['excerpt'] ?? ''));
                if ($excerpt === '') {
                    $excerpt = mb_substr(strip_tags((string) ($article['content'] ?? '')), 0, 160) . '...';
                }

                $imageUrl = $resolveImageUrl($article['image'] ?? null);
                $configuredAlt = trim((string) ($article['image_alt'] ?? ''));
                $imageAlt = 'Image de couverture de l\'article ' . $title;
                $categoryName = trim((string) ($article['category_name'] ?? ''));
                if ($categoryName !== '') {
                    $imageAlt .= ' (' . $categoryName . ')';
                }
                if ($configuredAlt !== '') {
                    $imageAlt = $configuredAlt;
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
                        <p class="meta">
                            <?= $h((string) ($article['published_at'] ?? 'Date inconnue')) ?>
                            <?php if ($categoryName !== ''): ?>
                                | <a href="/categorie/<?= $h((string) ($article['category_slug'] ?? '')) ?>"><?= $h($categoryName) ?></a>
                            <?php endif; ?>
                        </p>
                        <p class="excerpt"><?= $h($excerpt) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

    <nav class="pagination" aria-label="Pagination des articles">
        <?php if (($pagination['has_prev'] ?? false) === true): ?>
            <a class="btn secondary" href="/articles?<?= $h($buildQuery($currentPage - 1)) ?>">Page precedente</a>
        <?php endif; ?>

        <span>Page <?= $h($currentPage) ?> / <?= $h($totalPages) ?></span>

        <?php if (($pagination['has_next'] ?? false) === true): ?>
            <a class="btn secondary" href="/articles?<?= $h($buildQuery($currentPage + 1)) ?>">Page suivante</a>
        <?php endif; ?>
    </nav>
</main>
</body>
</html>
