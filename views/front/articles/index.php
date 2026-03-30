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
<div class="articles-page">
    <div class="articles-list-main">
        <h1 class="page-title">Tous les Articles</h1>
        <p class="page-subtitle">
            Explorez nos articles sur la situation actuelle en Iran. Filtrez par catégorie, recherchez par mot-clé ou consultez les dernières actualités.
        </p>

        <!-- Sidebar Filters -->
        <aside class="filters-sidebar" aria-label="Filtres">
            <section class="search-filters">
                <form method="get" action="/articles">
                    <div class="filter-group">
                        <label for="q">🔍 Recherche</label>
                        <input type="text" id="q" name="q" value="<?= $h($q) ?>" placeholder="Titre ou contenu...">
                    </div>

                    <div class="filter-group">
                        <label for="category">📂 Catégorie</label>
                        <select id="category" name="category">
                            <option value="">Toutes les catégories</option>
                            <?php foreach ($categories as $item): ?>
                                <?php $slug = (string) ($item['slug'] ?? ''); ?>
                                <option value="<?= $h($slug) ?>" <?= $slug === $category ? 'selected' : '' ?>>
                                    <?= $h((string) ($item['name'] ?? '')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="date">📅 Date</label>
                        <input type="date" id="date" name="date" value="<?= $h($date) ?>">
                    </div>

                    <div class="filter-buttons">
                        <button type="submit">Rechercher</button>
                        <a href="/articles">Réinitialiser</a>
                    </div>
                </form>
            </section>
        </aside>

        <!-- Articles Content -->
        <main class="articles-content">
            <p class="articles-count">
                <strong><?= $h((string)$totalArticles) ?></strong> article(s) trouvé(s)
            </p>

            <?php if ($articles === []): ?>
                <div class="no-articles-message">
                    <p>😔 Aucun article ne correspond à vos critères de recherche.</p>
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
                        $slug = (string) ($article['slug'] ?? '');
                        $categorySlug = (string) ($article['category_slug'] ?? '');
                        ?>
                        <article class="article-list-item">
                            <?php if ($imageUrl !== null): ?>
                                <img src="<?= $h($imageUrl) ?>" alt="<?= $h($imageAlt) ?>" class="article-list-image" loading="lazy" width="280" height="200">
                            <?php else: ?>
                                <div class="article-list-image">
                                    Aucune image
                                </div>
                            <?php endif; ?>

                            <div class="article-list-content">
                                <div>
                                    <h3><a href="/article/<?= $h($slug) ?>"><?= $h($title) ?></a></h3>
                                    <div class="article-list-meta">
                                        <span>📅 <?= $h($publishedAt) ?></span>
                                        <?php if ($categoryName !== ''): ?>
                                            <span>📂 <a href="/categorie/<?= $h($categorySlug) ?>"><?= $h($categoryName) ?></a></span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="excerpt"><?= $h($excerpt) ?></p>
                                </div>
                                <a href="/article/<?= $h($slug) ?>" class="read-more-link">Lire la suite →</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav class="pagination" aria-label="Pagination">
                    <?php if (($pagination['has_prev'] ?? false) === true): ?>
                        <a href="/articles?<?= $h($buildQuery($currentPage - 1)) ?>">← Précédente</a>
                    <?php endif; ?>

                    <span>
                        Page <strong><?= $h((string)$currentPage) ?></strong> / <strong><?= $h((string)$totalPages) ?></strong>
                    </span>

                    <?php if (($pagination['has_next'] ?? false) === true): ?>
                        <a href="/articles?<?= $h($buildQuery($currentPage + 1)) ?>">Suivante →</a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        </main>
    </div>
</div>
