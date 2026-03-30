<?php

use App\Core\ImageOptimizer;

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
    <!-- Section Hero : Article principal + Articles secondaires -->
    <section class="lm-hero">
        <div class="lm-container">
            <div class="lm-hero-grid">
                <?php if (!empty($mainArticle)): ?>
                    <!-- Article principal (gauche) -->
                    <article class="lm-main-article">
                        <a href="/article/<?= htmlspecialchars($mainArticle['slug']) ?>" class="lm-main-link">
                            <?php if (!empty($mainArticle['image'])):
                                $imageAttributes = $buildImageAttributes($mainArticle['image'] ?? null);
                            ?>
                                <figure class="lm-main-figure">
                                    <img
                                        src="<?= htmlspecialchars($imageAttributes['src']) ?>"
                                        <?= $imageAttributes['srcset'] !== '' ? 'srcset="' . htmlspecialchars($imageAttributes['srcset']) . '"' : '' ?>
                                        sizes="(max-width: 900px) 100vw, 900px"
                                        alt="<?= htmlspecialchars((string) ($mainArticle['image_alt'] ?? $mainArticle['title'])) ?>"
                                        width="1800"
                                        height="1080"
                                        class="lm-main-image"
                                        loading="eager"
                                        fetchpriority="high"
                                        decoding="async"
                                    >
                                </figure>
                            <?php endif; ?>
                            <div class="lm-main-content">
                                <?php if (!empty($mainArticle['category_name'])): ?>
                                    <span class="lm-category"><?= htmlspecialchars($mainArticle['category_name']) ?></span>
                                <?php endif; ?>
                                <h2 class="lm-main-title"><?= htmlspecialchars($mainArticle['title']) ?></h2>
                                <p class="lm-main-excerpt"><?= htmlspecialchars($mainArticle['excerpt'] ?? '') ?></p>
                            </div>
                        </a>
                    </article>
                <?php endif; ?>

                <!-- Articles secondaires (droite) -->
                <?php if (!empty($secondaryArticles)): ?>
                    <aside class="lm-secondary-articles">
                        <?php foreach ($secondaryArticles as $index => $article): ?>
                            <article class="lm-secondary-article<?= $index === 0 ? ' lm-secondary-featured' : '' ?>">
                                <a href="/article/<?= htmlspecialchars($article['slug']) ?>" class="lm-secondary-link">
                                    <?php if ($index === 0 && !empty($article['image'])):
                                        $imageAttributes = $buildImageAttributes($article['image'] ?? null);
                                    ?>
                                        <figure class="lm-secondary-figure">
                                            <img
                                                src="<?= htmlspecialchars($imageAttributes['src']) ?>"
                                                <?= $imageAttributes['srcset'] !== '' ? 'srcset="' . htmlspecialchars($imageAttributes['srcset']) . '"' : '' ?>
                                                sizes="(max-width: 600px) 100vw, 600px"
                                                alt="<?= htmlspecialchars((string) ($article['image_alt'] ?? $article['title'])) ?>"
                                                width="1800"
                                                height="1080"
                                                class="lm-secondary-image"
                                                loading="lazy"
                                                decoding="async"
                                            >
                                        </figure>
                                    <?php endif; ?>
                                    <div class="lm-secondary-content">
                                        <?php if (!empty($article['category_name'])): ?>
                                            <span class="lm-category lm-category-small"><?= htmlspecialchars($article['category_name']) ?></span>
                                        <?php endif; ?>
                                        <h3 class="lm-secondary-title"><?= htmlspecialchars($article['title']) ?></h3>
                                        <?php if ($index === 0): ?>
                                            <p class="lm-secondary-excerpt"><?= htmlspecialchars($article['excerpt'] ?? '') ?></p>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            </article>
                        <?php endforeach; ?>
                    </aside>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Section Articles récents -->
    <?php if (!empty($latestArticles)): ?>
        <section class="lm-latest">
            <div class="lm-container">
                <h2 class="lm-section-title">Derniers articles</h2>
                <div class="lm-latest-grid" id="articles-container">
                    <?php foreach ($latestArticles as $article): ?>
                        <article class="lm-latest-article">
                            <a href="/article/<?= htmlspecialchars($article['slug']) ?>" class="lm-latest-link">
                                <?php if (!empty($article['image'])):
                                    $imageAttributes = $buildImageAttributes($article['image'] ?? null);
                                ?>
                                    <figure class="lm-latest-figure">
                                        <img
                                            src="<?= htmlspecialchars($imageAttributes['src']) ?>"
                                            <?= $imageAttributes['srcset'] !== '' ? 'srcset="' . htmlspecialchars($imageAttributes['srcset']) . '"' : '' ?>
                                            sizes="(max-width: 600px) 100vw, 600px"
                                            alt="<?= htmlspecialchars((string) ($article['image_alt'] ?? $article['title'])) ?>"
                                            width="1800"
                                            height="1080"
                                            class="lm-latest-image"
                                            loading="lazy"
                                        >
                                    </figure>
                                <?php endif; ?>
                                <div class="lm-latest-content">
                                    <?php if (!empty($article['category_name'])): ?>
                                        <span class="lm-category lm-category-small"><?= htmlspecialchars($article['category_name']) ?></span>
                                    <?php endif; ?>
                                    <h3 class="lm-latest-title"><?= htmlspecialchars($article['title']) ?></h3>
                                    <p class="lm-latest-excerpt"><?= htmlspecialchars($article['excerpt'] ?? '') ?></p>
                                    <?php if (!empty($article['published_at'])): ?>
                                        <time class="lm-latest-date" datetime="<?= htmlspecialchars($article['published_at']) ?>">
                                            <?= date('d/m/Y', strtotime($article['published_at'])) ?>
                                        </time>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>

                <?php if ($totalArticles > count($latestArticles) + 5): ?>
                    <div id="scroll-sentinel" class="lm-scroll-sentinel"></div>
                    <div id="loader" class="lm-loader lm-loader-hidden" aria-live="polite" aria-atomic="true">
                        <div class="lm-loader-spinner"></div>
                        <span>Chargement...</span>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const articlesContainer = document.getElementById('articles-container');
    const sentinel = document.getElementById('scroll-sentinel');
    const loader = document.getElementById('loader');

    if (!articlesContainer || !sentinel) return;

    let currentPage = 2;
    let isLoading = false;
    let hasMore = true;

    function setLoaderVisible(visible) {
        if (!loader) return;
        loader.classList.toggle('lm-loader-visible', visible);
        loader.classList.toggle('lm-loader-hidden', !visible);
    }

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting && !isLoading && hasMore) {
                loadMoreArticles();
            }
        });
    }, {
        rootMargin: '200px'
    });

    observer.observe(sentinel);

    function getImageRenderData(rawPath) {
        const imagePath = (rawPath || '').trim();
        if (!imagePath) {
            return { src: '', srcset: '' };
        }

        if (/^https?:\/\//i.test(imagePath)) {
            return { src: imagePath, srcset: '' };
        }

        const normalizedPath = '/' + imagePath.replace(/^\/+/, '');
        const imgInfo = normalizedPath.match(/^(.*)\.([^.]+)$/);
        const imgBase = imgInfo ? imgInfo[1] : normalizedPath;
        const imgExt = imgInfo ? imgInfo[2] : 'webp';
        const srcMd = imgBase + '-md.' + imgExt;
        const srcSm = imgBase + '-sm.' + imgExt;

        return {
            src: srcSm,
            srcset: srcSm + ' 400w, ' + srcMd + ' 800w, ' + normalizedPath + ' 1800w'
        };
    }

    function loadMoreArticles() {
        isLoading = true;
        setLoaderVisible(true);

        fetch('/api/articles?page=' + currentPage)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.articles.length > 0) {
                    data.articles.forEach(function(article) {
                        var imageData = getImageRenderData(article.image);
                        var srcsetAttr = imageData.srcset ? `srcset="${escapeHtml(imageData.srcset)}"` : '';

                        const articleHtml = `
                            <article class="lm-latest-article">
                                <a href="/article/${escapeHtml(article.slug)}" class="lm-latest-link">
                                    ${article.image ? `
                                        <figure class="lm-latest-figure">
                                            <img
                                                src="${escapeHtml(imageData.src)}"
                                                ${srcsetAttr}
                                                sizes="(max-width: 600px) 100vw, 600px"
                                                alt="${escapeHtml(article.image_alt || article.title)}"
                                                width="1800"
                                                height="1080"
                                                class="lm-latest-image"
                                                loading="lazy"
                                            >
                                        </figure>
                                    ` : ''}
                                    <div class="lm-latest-content">
                                        ${article.category_name ? `<span class="lm-category lm-category-small">${escapeHtml(article.category_name)}</span>` : ''}
                                        <h3 class="lm-latest-title">${escapeHtml(article.title)}</h3>
                                        <p class="lm-latest-excerpt">${escapeHtml(article.excerpt || '')}</p>
                                        ${article.published_at ? `<time class="lm-latest-date" datetime="${escapeHtml(article.published_at)}">${formatDate(article.published_at)}</time>` : ''}
                                    </div>
                                </a>
                            </article>
                        `;
                        articlesContainer.insertAdjacentHTML('beforeend', articleHtml);
                    });

                    currentPage++;
                    hasMore = data.hasMore;

                    if (!hasMore) {
                        observer.disconnect();
                        sentinel.style.display = 'none';
                    }
                } else {
                    hasMore = false;
                    observer.disconnect();
                    sentinel.style.display = 'none';
                }

                isLoading = false;
                setLoaderVisible(false);
            })
            .catch(function() {
                isLoading = false;
                setLoaderVisible(false);
            });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    function formatDate(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleDateString('fr-FR');
    }
});
</script>
