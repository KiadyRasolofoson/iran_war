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
<div class="container articles-page">
    <div class="articles-list-main">
        <h1 class="page-title">Tous les Articles</h1>
        <p style="color: var(--text-light); margin-bottom: var(--spacing-2xl);">
            Trouvez les articles par catégorie, recherche ou date de publication.
        </p>

        <!-- Filtres -->
        <section class="search-filters" style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: var(--border-radius-lg); padding: var(--spacing-lg); margin-bottom: var(--spacing-2xl);">
            <form method="get" action="/articles" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--spacing-lg); align-items: end;">
                <div>
                    <label for="q" style="display: block; margin-bottom: var(--spacing-sm); font-weight: 600; font-family: var(--font-sans);">Recherche</label>
                    <input type="text" id="q" name="q" value="<?= $h($q) ?>" placeholder="Titre ou contenu..." style="width: 100%; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); padding: var(--spacing-sm); font-family: var(--font-body);">
                </div>

                <div>
                    <label for="category" style="display: block; margin-bottom: var(--spacing-sm); font-weight: 600; font-family: var(--font-sans);">Catégorie</label>
                    <select id="category" name="category" style="width: 100%; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); padding: var(--spacing-sm); font-family: var(--font-body);">
                        <option value="">Toutes les catégories</option>
                        <?php foreach ($categories as $item): ?>
                            <?php $slug = (string) ($item['slug'] ?? ''); ?>
                            <option value="<?= $h($slug) ?>" <?= $slug === $category ? 'selected' : '' ?>>
                                <?= $h((string) ($item['name'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="date" style="display: block; margin-bottom: var(--spacing-sm); font-weight: 600; font-family: var(--font-sans);">Date</label>
                    <input type="date" id="date" name="date" value="<?= $h($date) ?>" style="width: 100%; border: 1px solid var(--border-color); border-radius: var(--border-radius-sm); padding: var(--spacing-sm); font-family: var(--font-body);">
                </div>

                <div style="display: flex; gap: var(--spacing-md);">
                    <button type="submit" style="flex: 1; background-color: var(--accent); color: white; border: none; border-radius: var(--border-radius-sm); padding: var(--spacing-sm); font-weight: 600; cursor: pointer; font-family: var(--font-sans); text-transform: uppercase; font-size: 0.85rem;">Rechercher</button>
                    <a href="/articles" style="flex: 1; background-color: var(--card-bg); color: var(--accent); border: 2px solid var(--accent); border-radius: var(--border-radius-sm); padding: var(--spacing-sm); text-decoration: none; display: flex; align-items: center; justify-content: center; font-weight: 600; cursor: pointer; font-family: var(--font-sans); text-transform: uppercase; font-size: 0.85rem;">Réinitialiser</a>
                </div>
            </form>
        </section>

        <p style="color: var(--text-light); font-family: var(--font-sans); margin-bottom: var(--spacing-lg);">
            <strong><?= $h((string)$totalArticles) ?></strong> article(s) trouvé(s)
        </p>

        <?php if ($articles === []): ?>
            <div class="no-articles-message">
                <p>Aucun article ne correspond à vos critères de recherche.</p>
            </div>
        <?php else: ?>
            <section class="articles-list" aria-label="Liste des articles">
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
                    $publishedAt = (string) ($article['published_at'] ?? 'Date inconnue');
                    ?>
                    <article class="article-list-item">
                        <?php if ($imageUrl !== null): ?>
                            <img src="<?= $h($imageUrl) ?>" alt="<?= $h($imageAlt) ?>" class="article-list-image" loading="lazy">
                        <?php else: ?>
                            <div class="article-list-image" style="background: linear-gradient(135deg, var(--border-light) 0%, var(--border-color) 100%); display: flex; align-items: center; justify-content: center; color: var(--text-lighter);">
                                Aucune image
                            </div>
                        <?php endif; ?>

                        <div class="article-list-content">
                            <h3><a href="/article/<?= $h((string) ($article['slug'] ?? '')) ?>"><?= $h($title) ?></a></h3>
                            <div class="article-list-meta">
                                <span>📅 <?= $h($publishedAt) ?></span>
                                <?php if ($categoryName !== ''): ?>
                                    | <span>📂 <a href="/categorie/<?= $h((string) ($article['category_slug'] ?? '')) ?>" style="color: var(--accent);"><?= $h($categoryName) ?></a></span>
                                <?php endif; ?>
                            </div>
                            <p class="excerpt"><?= $h($excerpt) ?></p>
                            <a href="/article/<?= $h((string) ($article['slug'] ?? '')) ?>" class="read-more-link">Lire la suite →</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav class="pagination" aria-label="Pagination" style="margin-top: var(--spacing-3xl); display: flex; align-items: center; gap: var(--spacing-lg); justify-content: center; flex-wrap: wrap;">
                <?php if (($pagination['has_prev'] ?? false) === true): ?>
                    <a href="/articles?<?= $h($buildQuery($currentPage - 1)) ?>" style="background-color: var(--accent); color: white; padding: var(--spacing-sm) var(--spacing-lg); border-radius: var(--border-radius-sm); text-decoration: none; font-weight: 600; font-family: var(--font-sans);">← Précédente</a>
                <?php endif; ?>

                <span style="color: var(--text-light); font-family: var(--font-sans); font-weight: 600;">
                    Page <strong><?= $h((string)$currentPage) ?></strong> / <strong><?= $h((string)$totalPages) ?></strong>
                </span>

                <?php if (($pagination['has_next'] ?? false) === true): ?>
                    <a href="/articles?<?= $h($buildQuery($currentPage + 1)) ?>" style="background-color: var(--accent); color: white; padding: var(--spacing-sm) var(--spacing-lg); border-radius: var(--border-radius-sm); text-decoration: none; font-weight: 600; font-family: var(--font-sans);">Suivante →</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    </div>
</div>
