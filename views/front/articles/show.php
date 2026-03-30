<?php

declare(strict_types=1);

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
$imageAlt = trim((string) ($article['image_alt'] ?? ''));
if ($imageAlt === '') {
    $imageAlt = 'Image de couverture de l\'article ' . $title;
}
?>

<article class="article-detail">
    <!-- Fil d'ariane
     <nav class="breadcrumb" aria-label="Fil d'Ariane">
        <a href="/" title="Accueil">Accueil</a> 
        <span> / </span>
        <a href="/articles" title="Tous les articles">Articles</a>
        <?php if ($categoryName !== '' && $categorySlug !== ''): ?>
            <span> / </span>
            <a href="/categorie/<?= $h($categorySlug) ?>" title="<?= $h($categoryName) ?>"><?= $h($categoryName) ?></a>
        <?php endif; ?>
    </nav> --> 

    <!-- Header d'article -->
    <header class="article-header">
        <h1><?= $h($title) ?></h1>
        
        <!-- Métadonnées -->
        <div class="article-meta">
            <div class="article-meta-item" title="Date de publication">
                <span>📅</span>
                <time datetime="<?= $h($publishedAt) ?>"><?= $h($publishedAt) ?></time>
            </div>
            
            <?php if ($categoryName !== ''): ?>
                <div class="article-meta-item" title="Catégorie">
                    <span>📂</span>
                    <a href="/categorie/<?= $h($categorySlug) ?>" title="Articles de <?= $h($categoryName) ?>"><?= $h($categoryName) ?></a>
                </div>
            <?php endif; ?>
            
            <div class="article-meta-item" title="Temps de lecture estimé">
                <span>⏱️</span>
                <span><?= $readingTime ?> min de lecture</span>
            </div>
            
            <div class="article-meta-item" title="Auteur">
                <span>✍️</span>
                <span><?= $h($authorName) ?></span>
            </div>
        </div>
    </header>

    <!-- Image featured -->
    <?php if ($featuredImage !== null): ?>
        <figure>
            <img 
                src="<?= $h($featuredImage) ?>" 
                alt="<?= $h($imageAlt) ?>" 
                width="900"
                height="500"
                class="article-detail-image"
                loading="eager"
            >
            <?php if ($imageAlt !== ''): ?>
                <figcaption><?= $h($imageAlt) ?></figcaption>
            <?php endif; ?>
        </figure>
    <?php endif; ?>

    <!-- Contenu principal -->
    <div class="article-body editor-rich-content">
        <?= (string) ($article['content'] ?? '') ?>
    </div>

    <!-- Articles liés / Connexes -->
    <?php if ($relatedArticles !== [] && is_countable($relatedArticles) && count($relatedArticles) > 0): ?>
        <section class="related-articles" aria-label="Articles connexes">
            <h2>À Lire Aussi</h2>
            <div class="related-list">
                <?php foreach ($relatedArticles as $related): ?>
                    <?php
                        $relatedTitle = (string) ($related['title'] ?? 'Article');
                        $relatedSlug = (string) ($related['slug'] ?? '');
                        $relatedCategory = trim((string) ($related['category_name'] ?? ''));
                    ?>
                    <article class="related-item">
                        <a href="/article/<?= $h($relatedSlug) ?>" title="Lire l'article: <?= $h($relatedTitle) ?>">
                            <?= $h($relatedTitle) ?>
                        </a>
                        <?php if ($relatedCategory !== ''): ?>
                            <div class="related-category">
                                <?= $h($relatedCategory) ?>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
    
    <!-- Retour -->
    <div class="article-return">
        <a href="/articles" class="btn-read-more" title="Retour à la liste des articles">← Retour aux Articles</a>
    </div>
</article>
