<?php

declare(strict_types=1);

$category = is_array($category ?? null) ? $category : [];
$articles = is_array($articles ?? null) ? $articles : [];
$pagination = is_array($pagination ?? null) ? $pagination : [];
$filters = is_array($filters ?? null) ? $filters : [];

$h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$categoryName = (string) ($category['name'] ?? 'Categorie');
$categorySlug = (string) ($category['slug'] ?? '');

$currentPage = (int) ($pagination['page'] ?? 1);
$totalPages = (int) ($pagination['total_pages'] ?? 1);
$totalArticles = (int) ($pagination['total'] ?? 0);

$q = (string) ($filters['q'] ?? '');
$date = (string) ($filters['date'] ?? '');

$buildQuery = static function (int $page) use ($q, $date): string {
    $query = ['page' => $page];

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

    return '/' . ltrim($path, '/');
};
?>
<div style="max-width:1040px;margin:0 auto;padding:24px 16px 42px;">
    <header style="margin-bottom:16px;">
        <a href="/articles" style="color:#5f3d26;text-decoration:none;">Retour aux articles</a>
        <h1 style="margin:6px 0 8px;font-size:2.2rem;">Categorie: <?= $h($categoryName) ?></h1>
        <?php if ((string) ($category['description'] ?? '') !== ''): ?>
            <p style="margin:0;color:#6b7280;"><?= $h((string) ($category['description'] ?? '')) ?></p>
        <?php endif; ?>
    </header>

    <section aria-label="Filtres de categorie" style="margin-top:16px;background:#fff;border:1px solid #d6d3d1;border-radius:12px;padding:12px;">
        <form method="get" action="/categorie/<?= $h($categorySlug) ?>" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:10px;align-items:end;">
            <div>
                <label for="q" style="display:block;margin-bottom:4px;color:#6b7280;">Recherche dans la categorie</label>
                <input type="text" id="q" name="q" value="<?= $h($q) ?>" placeholder="Titre ou contenu" style="width:100%;border:1px solid #d6d3d1;border-radius:8px;padding:8px 10px;">
            </div>

            <div>
                <label for="date" style="display:block;margin-bottom:4px;color:#6b7280;">Date</label>
                <input type="date" id="date" name="date" value="<?= $h($date) ?>" style="width:100%;border:1px solid #d6d3d1;border-radius:8px;padding:8px 10px;">
            </div>

            <div>
                <button type="submit" style="border:1px solid #5f3d26;border-radius:8px;background:#5f3d26;color:#fff;padding:9px 12px;cursor:pointer;">Filtrer</button>
                <a href="/categorie/<?= $h($categorySlug) ?>" style="border:1px solid #5f3d26;border-radius:8px;background:#fff;color:#5f3d26;padding:9px 12px;text-decoration:none;display:inline-block;">Reinitialiser</a>
            </div>
        </form>
    </section>

    <p style="margin:12px 0 16px;color:#6b7280;"><?= $h($totalArticles) ?> article(s) dans cette categorie.</p>

    <?php if ($articles === []): ?>
        <p style="background:#f8e7d7;border:1px solid #e9bf98;border-radius:10px;padding:14px;">Aucun article publie dans cette categorie avec ces filtres.</p>
    <?php else: ?>
        <section aria-label="Articles de categorie" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:14px;">
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
                <article style="background:#fff;border:1px solid #d6d3d1;border-radius:12px;overflow:hidden;display:grid;grid-template-rows:155px auto;">
                    <?php if ($imageUrl !== null): ?>
                        <img src="<?= $h($imageUrl) ?>" alt="<?= $h($imageAlt) ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover;background:#e7e5e4;">
                    <?php else: ?>
                        <div role="img" aria-label="<?= $h($imageAlt) ?>" style="background:#e7e5e4;"></div>
                    <?php endif; ?>
                    <div style="padding:12px;">
                        <h2 style="margin:0 0 8px;font-size:1.1rem;"><a href="/article/<?= $h((string) ($article['slug'] ?? '')) ?>" style="color:inherit;text-decoration:none;"><?= $h($title) ?></a></h2>
                        <p style="margin:0 0 8px;color:#6b7280;font-size:0.9rem;">Publie le <?= $h((string) ($article['published_at'] ?? 'Date inconnue')) ?></p>
                        <p style="margin:0;"><?= $h($excerpt) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

    <nav aria-label="Pagination categorie" style="margin-top:18px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        <?php if (($pagination['has_prev'] ?? false) === true): ?>
            <a href="/categorie/<?= $h($categorySlug) ?>?<?= $h($buildQuery($currentPage - 1)) ?>" style="border:1px solid #5f3d26;border-radius:8px;background:#fff;color:#5f3d26;padding:9px 12px;text-decoration:none;display:inline-block;">Page precedente</a>
        <?php endif; ?>

        <span style="color:#6b7280;">Page <?= $h($currentPage) ?> / <?= $h($totalPages) ?></span>

        <?php if (($pagination['has_next'] ?? false) === true): ?>
            <a href="/categorie/<?= $h($categorySlug) ?>?<?= $h($buildQuery($currentPage + 1)) ?>" style="border:1px solid #5f3d26;border-radius:8px;background:#fff;color:#5f3d26;padding:9px 12px;text-decoration:none;display:inline-block;">Page suivante</a>
        <?php endif; ?>
    </nav>
</div>
