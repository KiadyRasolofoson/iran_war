<?php

declare(strict_types=1);

use App\Core\ImageOptimizer;

$category = is_array($category ?? null) ? $category : [];
$articles = is_array($articles ?? null) ? $articles : [];
$pagination = is_array($pagination ?? null) ? $pagination : [];
$filters = is_array($filters ?? null) ? $filters : [];

$h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$categoryName = (string) ($category['name'] ?? 'Catégorie');
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

$isAbsoluteImageUrl = static fn(string $path): bool => str_starts_with($path, 'http://') || str_starts_with($path, 'https://');

$buildImageAttributes = static function (?string $rawPath) use ($isAbsoluteImageUrl): array {
    $imagePath = trim((string) $rawPath);
    if ($imagePath === '') {
        return [
            'src' => '',
            'srcset' => '',
        ];
    }

    if ($isAbsoluteImageUrl($imagePath)) {
        return [
            'src' => $imagePath,
            'srcset' => '',
        ];
    }

    $normalizedPath = '/' . ltrim($imagePath, '/');

    return [
        'src' => $normalizedPath,
        'srcset' => ImageOptimizer::getResponsiveSrcset($imagePath, ''),
    ];
};
?>

<div class="lm-home">
    <!-- En-tête de catégorie -->
    <section class="lm-category-header">
        <div class="lm-container">
            <nav class="lm-breadcrumb" style="margin-bottom: var(--spacing-md); font-size: 0.9rem;">
                <a href="/" style="color: var(--primary);">Accueil</a> /
                <a href="/articles" style="color: var(--primary);">Articles</a> /
                <span style="color: var(--text-light);"><?= $h($categoryName) ?></span>
            </nav>
            <h1 class="lm-category-title" style="margin: 0 0 var(--spacing-md); color: var(--primary-dark); font-size: 2.5rem; font-weight: 700;"><?= $h($categoryName) ?></h1>
            <?php if ((string) ($category['description'] ?? '') !== ''): ?>
                <p class="lm-category-description" style="margin: 0 0 var(--spacing-xl); color: var(--text-light); font-size: 1.1rem; line-height: 1.6;"><?= $h((string) ($category['description'] ?? '')) ?></p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Filtres -->
    <section class="lm-filters">
        <div class="lm-container">
            <div class="lm-filters-card" style="background: var(--card-bg); border: 1px solid var(--border-color); border-radius: var(--border-radius-lg); padding: var(--spacing-xl); margin-bottom: var(--spacing-2xl); box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <form method="get" action="/categorie/<?= $h($categorySlug) ?>" class="lm-filters-form" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: var(--spacing-lg); align-items: end;">
                    <div class="lm-filter-group">
                        <label for="q" class="lm-filter-label" style="display: block; margin-bottom: var(--spacing-sm); font-weight: 600; color: var(--primary-dark); font-family: var(--font-sans);">Recherche</label>
                        <input type="text" id="q" name="q" value="<?= $h($q) ?>" placeholder="Rechercher dans les articles..." class="lm-filter-input" style="width: 100%; border: 2px solid var(--border-color); border-radius: var(--border-radius-sm); padding: var(--spacing-md); font-family: var(--font-body); transition: border-color 0.3s ease; font-size: 1rem;">
                    </div>

                    <div class="lm-filter-group">
                        <label for="date" class="lm-filter-label" style="display: block; margin-bottom: var(--spacing-sm); font-weight: 600; color: var(--primary-dark); font-family: var(--font-sans);">Date de publication</label>
                        <input type="date" id="date" name="date" value="<?= $h($date) ?>" class="lm-filter-input" style="width: 100%; border: 2px solid var(--border-color); border-radius: var(--border-radius-sm); padding: var(--spacing-md); font-family: var(--font-body); transition: border-color 0.3s ease; font-size: 1rem;">
                    </div>

                    <div class="lm-filter-actions" style="display: flex; gap: var(--spacing-md);">
                        <button type="submit" class="lm-btn lm-btn-primary" style="flex: 1; background: linear-gradient(135deg, var(--accent), var(--primary)); color: white; border: none; border-radius: var(--border-radius-sm); padding: var(--spacing-md) var(--spacing-lg); font-weight: 600; cursor: pointer; font-family: var(--font-sans); text-transform: uppercase; font-size: 0.9rem; letter-spacing: 0.5px; transition: transform 0.2s ease;">Filtrer</button>
                        <a href="/categorie/<?= $h($categorySlug) ?>" class="lm-btn lm-btn-outline" style="flex: 1; background-color: transparent; color: var(--accent); border: 2px solid var(--accent); border-radius: var(--border-radius-sm); padding: var(--spacing-md) var(--spacing-lg); text-decoration: none; display: flex; align-items: center; justify-content: center; font-weight: 600; font-family: var(--font-sans); text-transform: uppercase; font-size: 0.9rem; letter-spacing: 0.5px; transition: all 0.2s ease;">Réinitialiser</a>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Articles de la catégorie -->
    <section class="lm-latest">
        <div class="lm-container">
            <div class="lm-section-header" style="margin-bottom: var(--spacing-xl);">
                <h2 class="lm-section-title" style="font-size: 1.8rem; font-weight: 700; color: var(--primary-dark); margin: 0;"><?= $h($categoryName) ?></h2>
                <p class="lm-section-meta" style="color: var(--text-light); font-family: var(--font-sans); margin: var(--spacing-sm) 0 0;">
                    <strong><?= $h((string)$totalArticles) ?></strong> article<?= $totalArticles !== 1 ? 's' : '' ?> dans cette catégorie
                </p>
            </div>

            <?php if ($articles === []): ?>
                <div class="lm-no-articles" style="text-align: center; padding: var(--spacing-3xl); background: var(--card-bg); border-radius: var(--border-radius-lg); border: 1px solid var(--border-color);">
                    <div style="font-size: 3rem; margin-bottom: var(--spacing-lg); color: var(--text-lighter);">📄</div>
                    <h3 style="color: var(--primary-dark); margin-bottom: var(--spacing-md);">Aucun article trouvé</h3>
                    <p style="color: var(--text-light); margin: 0;">Aucun article ne correspond à vos critères de recherche.</p>
                </div>
            <?php else: ?>
                <div class="lm-latest-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: var(--spacing-2xl); margin-bottom: var(--spacing-3xl);">
                    <?php foreach ($articles as $article): ?>
                        <article class="lm-latest-article">
                            <a href="/article/<?= $h((string) ($article['slug'] ?? '')) ?>" class="lm-latest-link">
                                <?php if (!empty($article['image'])):
                                    $imageAttributes = $buildImageAttributes($article['image'] ?? null);
                                ?>
                                    <figure class="lm-latest-figure">
                                        <img
                                            src="<?= $h($imageAttributes['src']) ?>"
                                            <?= $imageAttributes['srcset'] !== '' ? 'srcset="' . $h($imageAttributes['srcset']) . '"' : '' ?>
                                            sizes="(max-width: 600px) 100vw, 600px"
                                            alt="<?= $h((string) ($article['image_alt'] ?? $article['title'])) ?>"
                                            width="1800"
                                            height="1080"
                                            class="lm-latest-image"
                                            loading="lazy"
                                        >
                                    </figure>
                                <?php else: ?>
                                    <figure class="lm-latest-figure">
                                        <div class="lm-latest-image lm-no-image" style="background: linear-gradient(135deg, var(--border-light) 0%, var(--border-color) 100%); display: flex; align-items: center; justify-content: center; color: var(--text-lighter); font-size: 1.2rem; aspect-ratio: 16/9;">
                                            📰
                                        </div>
                                    </figure>
                                <?php endif; ?>
                                <div class="lm-latest-content">
                                    <?php if (!empty($article['category_name'])): ?>
                                        <span class="lm-category lm-category-small"><?= $h($article['category_name']) ?></span>
                                    <?php endif; ?>
                                    <h3 class="lm-latest-title"><?= $h((string) ($article['title'] ?? 'Sans titre')) ?></h3>
                                    <p class="lm-latest-excerpt"><?= $h(trim((string) ($article['excerpt'] ?? ''))) ?: $h(mb_substr(strip_tags((string) ($article['content'] ?? '')), 0, 160) . '...') ?></p>
                                    <?php if (!empty($article['published_at'])): ?>
                                        <time class="lm-latest-date" datetime="<?= $h($article['published_at']) ?>">
                                            <?= date('d/m/Y', strtotime($article['published_at'])) ?>
                                        </time>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav class="lm-pagination" aria-label="Navigation des pages" style="display: flex; align-items: center; justify-content: center; gap: var(--spacing-lg); margin-top: var(--spacing-3xl); padding-top: var(--spacing-2xl); border-top: 1px solid var(--border-color);">
                    <?php if (($pagination['has_prev'] ?? false) === true): ?>
                        <a href="/categorie/<?= $h($categorySlug) ?>?<?= $h($buildQuery($currentPage - 1)) ?>" class="lm-pagination-btn lm-pagination-prev" style="background: linear-gradient(135deg, var(--accent), var(--primary)); color: white; padding: var(--spacing-md) var(--spacing-xl); border-radius: var(--border-radius-sm); text-decoration: none; font-weight: 600; font-family: var(--font-sans); transition: transform 0.2s ease; display: flex; align-items: center; gap: var(--spacing-sm);">
                            ← Précédente
                        </a>
                    <?php endif; ?>

                    <span class="lm-pagination-info" style="color: var(--text-light); font-family: var(--font-sans); font-weight: 600; padding: 0 var(--spacing-lg);">
                        Page <strong style="color: var(--primary-dark);"><?= $h((string)$currentPage) ?></strong> sur <strong style="color: var(--primary-dark);"><?= $h((string)$totalPages) ?></strong>
                    </span>

                    <?php if (($pagination['has_next'] ?? false) === true): ?>
                        <a href="/categorie/<?= $h($categorySlug) ?>?<?= $h($buildQuery($currentPage + 1)) ?>" class="lm-pagination-btn lm-pagination-next" style="background: linear-gradient(135deg, var(--accent), var(--primary)); color: white; padding: var(--spacing-md) var(--spacing-xl); border-radius: var(--border-radius-sm); text-decoration: none; font-weight: 600; font-family: var(--font-sans); transition: transform 0.2s ease; display: flex; align-items: center; gap: var(--spacing-sm);">
                            Suivante →
                        </a>
                    <?php endif; ?>
                </nav>
            <?php endif; ?>
        </div>
    </section>
</div>

<style>
/* Ajout de styles spécifiques pour les catégories */
.lm-filter-input:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(var(--accent-rgb, 66, 123, 214), 0.1);
}

.lm-btn-primary:hover {
    transform: translateY(-2px);
}

.lm-btn-outline:hover {
    background-color: var(--accent);
    color: white;
    transform: translateY(-2px);
}

.lm-pagination-btn:hover {
    transform: translateY(-2px);
}

.lm-category-header {
    padding: var(--spacing-2xl) 0;
    background: linear-gradient(135deg, var(--card-bg) 0%, var(--background) 100%);
    border-bottom: 1px solid var(--border-color);
}

.lm-filters {
    padding: var(--spacing-xl) 0 0;
}

.lm-latest {
    padding: var(--spacing-2xl) 0;
}
</style>
