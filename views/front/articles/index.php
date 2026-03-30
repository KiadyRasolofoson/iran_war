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

$buildQuery = static function (int $page) use ($q, $category, $date): string {
    $query = ['page' => $page];

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

    return '/' . ltrim($path, '/');
};
?>
<div class="shell" style="max-width:1100px;margin:0 auto;padding:24px 16px 48px;">
    <h1 style="margin:0 0 10px;font-size:2rem;line-height:1.2;">Articles publies</h1>
    <p style="margin:0 0 18px;color:#6b7280;">Explorez les analyses par mots-cles, categorie et date de publication.</p>

    <section style="background:#fff;border:1px solid #d6d3d1;border-radius:12px;padding:14px;margin-bottom:20px;" aria-label="Filtres de recherche">
        <form method="get" action="/articles" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:10px;align-items:end;">
            <div>
                <label for="q" style="display:block;margin-bottom:4px;color:#6b7280;">Recherche</label>
                <input type="text" id="q" name="q" value="<?= $h($q) ?>" placeholder="Titre ou contenu" style="width:100%;border:1px solid #d6d3d1;border-radius:8px;padding:8px 10px;">
            </div>

            <div>
                <label for="category" style="display:block;margin-bottom:4px;color:#6b7280;">Categorie</label>
                <select id="category" name="category" style="width:100%;border:1px solid #d6d3d1;border-radius:8px;padding:8px 10px;">
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
                <label for="date" style="display:block;margin-bottom:4px;color:#6b7280;">Date</label>
                <input type="date" id="date" name="date" value="<?= $h($date) ?>" style="width:100%;border:1px solid #d6d3d1;border-radius:8px;padding:8px 10px;">
            </div>

            <div>
                <button type="submit" style="border:1px solid #8b4513;border-radius:8px;background:#8b4513;color:#fff;padding:9px 12px;cursor:pointer;">Filtrer</button>
                <a href="/articles" style="border:1px solid #8b4513;border-radius:8px;background:#fff;color:#8b4513;padding:9px 12px;text-decoration:none;display:inline-block;">Reinitialiser</a>
            </div>
        </form>
    </section>

    <p style="margin:8px 0 18px;color:#6b7280;"><?= $h($totalArticles) ?> article(s) trouves.</p>

    <?php if ($articles === []): ?>
        <p style="background:#fce7d5;border:1px solid #f1c9a5;border-radius:10px;padding:14px;">Aucun article publie ne correspond a ces filtres.</p>
    <?php else: ?>
        <section style="display:grid;grid-template-columns:repeat(auto-fit,minmax(255px,1fr));gap:14px;" aria-label="Liste des articles">
            <?php foreach ($articles as $article): ?>
                <?php
                $title = (string) ($article['title'] ?? 'Sans titre');
                $excerpt = trim((string) ($article['excerpt'] ?? ''));
                if ($excerpt === '') {
                    $excerpt = mb_substr(strip_tags((string) ($article['content'] ?? '')), 0, 160) . '...';
                }

                $imageUrl = $resolveImageUrl($article['image'] ?? null);
                $configuredAlt = trim((string) ($article['image_alt'] ?? ''));
                $imageAlt = $configuredAlt !== '' ? $configuredAlt : 'Image de couverture de l\'article ' . $title;
                $categoryName = trim((string) ($article['category_name'] ?? ''));
                ?>
                <article style="background:#fff;border:1px solid #d6d3d1;border-radius:12px;overflow:hidden;display:grid;grid-template-rows:160px auto;">
                    <?php if ($imageUrl !== null): ?>
                        <img src="<?= $h($imageUrl) ?>" alt="<?= $h($imageAlt) ?>" loading="lazy" style="width:100%;height:100%;object-fit:cover;background:#e7e5e4;">
                    <?php else: ?>
                        <div role="img" aria-label="<?= $h($imageAlt) ?>" style="background:#e7e5e4;"></div>
                    <?php endif; ?>

                    <div style="padding:12px;">
                        <h2 style="margin:0 0 8px;font-size:1.15rem;"><a href="/article/<?= $h((string) ($article['slug'] ?? '')) ?>" style="color:inherit;text-decoration:none;"><?= $h($title) ?></a></h2>
                        <p style="margin:0 0 8px;font-size:0.9rem;color:#6b7280;">
                            <?= $h((string) ($article['published_at'] ?? 'Date inconnue')) ?>
                            <?php if ($categoryName !== ''): ?>
                                | <a href="/categorie/<?= $h((string) ($article['category_slug'] ?? '')) ?>"><?= $h($categoryName) ?></a>
                            <?php endif; ?>
                        </p>
                        <p style="margin:0;line-height:1.45;"><?= $h($excerpt) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

    <nav aria-label="Pagination des articles" style="margin-top:20px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
        <?php if (($pagination['has_prev'] ?? false) === true): ?>
            <a href="/articles?<?= $h($buildQuery($currentPage - 1)) ?>" style="border:1px solid #8b4513;border-radius:8px;background:#fff;color:#8b4513;padding:9px 12px;text-decoration:none;display:inline-block;">Page precedente</a>
        <?php endif; ?>

        <span style="color:#6b7280;">Page <?= $h($currentPage) ?> / <?= $h($totalPages) ?></span>

        <?php if (($pagination['has_next'] ?? false) === true): ?>
            <a href="/articles?<?= $h($buildQuery($currentPage + 1)) ?>" style="border:1px solid #8b4513;border-radius:8px;background:#fff;color:#8b4513;padding:9px 12px;text-decoration:none;display:inline-block;">Page suivante</a>
        <?php endif; ?>
    </nav>
</div>
