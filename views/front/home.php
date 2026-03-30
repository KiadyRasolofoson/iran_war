<div class="home-page">
    <section class="hero-section">
        <div class="container">
            <h1 class="page-title">À la Une</h1>
            
            <?php if (!empty($mainArticle)): ?>
                <article class="main-article">
                    <?php if (!empty($mainArticle['image'])): ?>
                        <img
                            src="/<?= htmlspecialchars(ltrim((string) $mainArticle['image'], '/')) ?>"
                            alt="<?= htmlspecialchars((string) ($mainArticle['image_alt'] ?? $mainArticle['title'])) ?>"
                            width="800"
                            height="600"
                            class="article-image"
                            loading="eager"
                        >
                    <?php endif; ?>
                    <div class="main-article-content">
                        <div style="font-family: var(--font-sans); font-size: 0.85rem; color: var(--accent); text-transform: uppercase; letter-spacing: 1px; margin-bottom: var(--spacing-md); font-weight: 700;">Actualité principale</div>
                        <h2>
                            <a href="/article/<?= htmlspecialchars($mainArticle['slug']) ?>">
                                <?= htmlspecialchars($mainArticle['title']) ?>
                            </a>
                        </h2>
                        <p class="excerpt"><?= htmlspecialchars($mainArticle['excerpt']) ?></p>
                        <a href="/article/<?= htmlspecialchars($mainArticle['slug']) ?>" class="btn-read-more" aria-label="Lire l'article: <?= htmlspecialchars($mainArticle['title']) ?>">Lire l'article</a>
                    </div>
                </article>
            <?php endif; ?>

    </section>

    <section class="latest-articles-section container">
        <h2 class="section-title">Dernières Actualités</h2>
        <div class="articles-grid">
            <?php if (!empty($latestArticles)): ?>
                <?php foreach ($latestArticles as $article): ?>
                    <article class="article-card">
                        <?php if (!empty($article['image'])): ?>
                            <img
                                src="/<?= htmlspecialchars(ltrim((string) $article['image'], '/')) ?>"
                                alt="<?= htmlspecialchars((string) ($article['image_alt'] ?? $article['title'])) ?>"
                                width="400"
                                height="300"
                                class="article-card-image"
                                loading="lazy"
                            >
                        <?php else: ?>
                            <div class="article-card-image" style="background-color: var(--border-light); display: flex; align-items: center; justify-content: center; color: var(--text-lighter);">
                                Aucune image
                            </div>
                        <?php endif; ?>
                        <div class="article-card-content">
                            <h3>
                                <a href="/article/<?= htmlspecialchars($article['slug']) ?>">
                                    <?= htmlspecialchars($article['title']) ?>
                                </a>
                            </h3>
                            <p class="excerpt"><?= htmlspecialchars($article['excerpt']) ?></p>
                            <a href="/article/<?= htmlspecialchars($article['slug']) ?>" class="read-more-link">Lire la suite →</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-articles-message">
                    <p>Aucun article récent à afficher pour le moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>
