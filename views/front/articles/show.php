<?php

declare(strict_types=1);

use App\Core\ImageOptimizer;

$article = is_array($article ?? null) ? $article : [];
$relatedArticles = is_array($relatedArticles ?? null) ? $relatedArticles : [];

$h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$title = (string) ($article['title'] ?? 'Article');
$categoryName = trim((string) ($article['category_name'] ?? ''));
$categorySlug = (string) ($article['category_slug'] ?? '');
$authorName = trim((string) ($article['author_name'] ?? 'Rédaction'));
$publishedAt = (string) ($article['published_at'] ?? 'Date inconnue');
$readingTime = isset($article['content']) ? max(1, (int)ceil(str_word_count(strip_tags((string) $article['content'])) / 200)) : 5;

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

$featuredImage = $resolveImageUrl($article['image'] ?? null);
$isFeaturedImageExternal = $featuredImage !== null
    && (str_starts_with($featuredImage, 'http://') || str_starts_with($featuredImage, 'https://'));
$imageAlt = trim((string) ($article['image_alt'] ?? ''));
if ($imageAlt === '') {
    $imageAlt = 'Image de couverture de l\'article ' . $title;
}
?>

<article class="lm-article-page">
    <div class="lm-article-container">
        <!-- Header d'article -->
        <header class="lm-article-header">
            <h1 class="lm-article-title"><?= $h($title) ?></h1>

            <!-- Chapeau / Lead -->
            <?php if (isset($article['excerpt']) && trim((string)$article['excerpt']) !== ''): ?>
                <p class="lm-article-lead"><?= $h((string)$article['excerpt']) ?></p>
            <?php endif; ?>
        </header>

        <!-- Métadonnées -->
        <div class="lm-article-meta">
            <span class="lm-article-author">Par <?= $h($authorName) ?></span>
            <span class="lm-article-separator">•</span>
            <time class="lm-article-date" datetime="<?= $h($publishedAt) ?>">
                Publié le <?= $h($publishedAt) ?>
            </time>
            <span class="lm-article-separator">•</span>
            <span class="lm-article-reading-time">Lecture <?= $readingTime ?> min</span>
        </div>

        <!-- Image principale -->
        <?php if ($featuredImage !== null): ?>
            <figure class="lm-article-figure">
                <img
                    src="<?= $h($featuredImage) ?>"
                    <?= !$isFeaturedImageExternal ? 'srcset="' . $h(ImageOptimizer::getResponsiveSrcset((string) ($article['image'] ?? ''), '')) . '"' : '' ?>
                    sizes="(max-width: 900px) 100vw, 900px"
                    alt="<?= $h($imageAlt) ?>"
                    class="lm-article-image"
                    loading="eager"
                >
                <?php if ($imageAlt !== ''): ?>
                    <figcaption class="lm-article-caption"><?= $h($imageAlt) ?></figcaption>
                <?php endif; ?>
            </figure>
        <?php endif; ?>

        <!-- Layout deux colonnes -->
        <div class="lm-article-layout">
            <!-- Colonne principale : Contenu -->
            <div class="lm-article-main">
                <div class="lm-article-content editor-rich-content">
                    <?= (string) ($article['content'] ?? '') ?>
                </div>
            </div>

            <!-- Colonne latérale : Articles liés -->
            <?php if ($relatedArticles !== [] && is_countable($relatedArticles) && count($relatedArticles) > 0): ?>
                <aside class="lm-article-sidebar">
                    <div class="lm-article-related">
                        <h2 class="lm-related-title">À lire également</h2>
                        <div class="lm-related-list">
                            <?php foreach ($relatedArticles as $related): ?>
                                <?php
                                    $relatedTitle = (string) ($related['title'] ?? 'Article');
                                    $relatedSlug = (string) ($related['slug'] ?? '');
                                    $relatedCategory = trim((string) ($related['category_name'] ?? ''));
                                ?>
                                <article class="lm-related-item">
                                    <?php if ($relatedCategory !== ''): ?>
                                        <span class="lm-related-category"><?= strtoupper($h($relatedCategory)) ?></span>
                                    <?php endif; ?>
                                    <h3 class="lm-related-item-title">
                                        <a href="/article/<?= $h($relatedSlug) ?>" title="Lire l'article: <?= $h($relatedTitle) ?>">
                                            <?= $h($relatedTitle) ?>
                                        </a>
                                    </h3>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </aside>
            <?php endif; ?>
        </div>
    </div>
</article>
