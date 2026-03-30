<div class="home-page">
    <section class="hero-section">
        <div class="container">
            <!-- Balise H1 unique pour la page d'accueil -->
            <h1 class="page-title">À la une : Toute l'actualité en temps réel</h1>
            
            <?php if (!empty($mainArticle)): ?>
                <article class="main-article card">
                    <div class="main-article-content">
                        <?php if (!empty($mainArticle['image'])): ?>
                            <p>
                                <img
                                    src="/<?= htmlspecialchars(ltrim((string) $mainArticle['image'], '/')) ?>"
                                    alt="<?= htmlspecialchars((string) ($mainArticle['image_alt'] ?? $mainArticle['title'])) ?>"
                                    style="max-width:100%;height:auto;"
                                >
                            </p>
                        <?php endif; ?>
                        <h2>
                            <a href="/article/<?= htmlspecialchars($mainArticle['slug']) ?>">
                                <?= htmlspecialchars($mainArticle['title']) ?>
                            </a>
                        </h2>
                        <p class="excerpt"><?= htmlspecialchars($mainArticle['excerpt']) ?></p>
                        <a href="/article/<?= htmlspecialchars($mainArticle['slug']) ?>" class="btn-read-more" aria-label="Lire l'article: <?= htmlspecialchars($mainArticle['title']) ?>">Lire l'article complet</a>
                    </div>
                </article>
            <?php endif; ?>
        </div>
    </section>

    <section class="latest-articles-section container">
        <h2 class="section-title">Dernières actualités</h2>
        <div class="articles-grid">
            <?php if (!empty($latestArticles)): ?>
                <?php foreach ($latestArticles as $article): ?>
                    <article class="article-card card">
                        <div class="article-card-content">
                            <h3>
                                <a href="/article/<?= htmlspecialchars($article['slug']) ?>">
                                    <?= htmlspecialchars($article['title']) ?>
                                </a>
                            </h3>
                            <p class="excerpt"><?= htmlspecialchars($article['excerpt']) ?></p>
                            <a href="/article/<?= htmlspecialchars($article['slug']) ?>" class="read-more-link" aria-label="Lire la suite de l'article: <?= htmlspecialchars($article['title']) ?>">Lire la suite</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Aucun article récent à afficher pour le moment.</p>
            <?php endif; ?>
        </div>
    </section>
</div>
