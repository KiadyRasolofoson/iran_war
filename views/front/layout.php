<?php

declare(strict_types=1);

$pageTitle = isset($pageTitle) && is_string($pageTitle) && $pageTitle !== '' ? $pageTitle : 'Actualités - Conflit Iran-Irak | Couverture Complète';
$metaDescription = isset($metaDescription) && is_string($metaDescription) && $metaDescription !== ''
    ? $metaDescription
    : 'Couverture journalistique complète du conflit Iran-Irak : analyses approfondies, dossiers spécialisés, et actualités en temps réel. Reportages professionnels et fiables.';
$ogImage = isset($ogImage) && is_string($ogImage) && $ogImage !== '' ? $ogImage : '/assets/images/default-og.jpg';
$content = isset($content) && is_string($content) ? $content : '';

$escape = static fn(string|int $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

// Récupération dynamique des catégories avec nombre d'articles
$navbarCategories = [];
try {
    $categoryModel = new \App\Models\Category();
    $navbarCategories = $categoryModel->listWithPublishedArticleCount();

    // Filtrer les catégories qui ont au moins un article
    $navbarCategories = array_filter($navbarCategories, function($category) {
        return isset($category['article_count']) && (int)$category['article_count'] > 0;
    });

    // Trier par nombre d'articles décroissant
    usort($navbarCategories, function($a, $b) {
        $countA = isset($a['article_count']) ? (int)$a['article_count'] : 0;
        $countB = isset($b['article_count']) ? (int)$b['article_count'] : 0;
        return $countB - $countA; // Décroissant
    });

    // Si aucune catégorie trouvée après le filtre, laisser le tableau vide
    // Pas de fallback avec des catégories à 0 articles
} catch (Exception $e) {
    // En cas d'erreur, laisser un tableau vide
    $navbarCategories = [];
}

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
$uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
$canonicalUrl = $scheme . '://' . $host . $uri;
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1a1410">
    <meta name="color-scheme" content="light">
    
    <!-- SEO & Meta Tags -->
    <title><?= $escape($pageTitle) ?></title>
    <meta name="description" content="<?= $escape($metaDescription) ?>">
    <meta name="keywords" content="Iran, Irak, conflit, guerre, actualités, journalisme, couverture">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="google-site-verification" content="">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:title" content="<?= $escape($pageTitle) ?>">
    <meta property="og:description" content="<?= $escape($metaDescription) ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= $escape($canonicalUrl) ?>">
    <meta property="og:image" content="<?= $escape($ogImage) ?>">
    <meta property="og:locale" content="fr_FR">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= $escape($pageTitle) ?>">
    <meta name="twitter:description" content="<?= $escape($metaDescription) ?>">
    <meta name="twitter:image" content="<?= $escape($ogImage) ?>">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="<?= $escape($canonicalUrl) ?>">

    <!-- Preload LCP image if available -->
    <?php if (isset($mainArticle['image']) && !empty($mainArticle['image'])): ?>
    <link rel="preload" as="image" href="/<?= $escape(ltrim((string)$mainArticle['image'], '/')) ?>" fetchpriority="high">
    <?php endif; ?>

    <style>
    :root{--font-serif:'Playfair Display',Georgia,Times,'Times New Roman',serif;--font-body:Georgia,Times,serif;--font-sans:'Inter',system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;--accent:#c41e3a;--text-color:#0f0e0c}
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    html{scroll-behavior:smooth}
    body{font-family:var(--font-body);color:var(--text-color);background:#f5f3f0;line-height:1.7;display:flex;flex-direction:column;min-height:100vh;font-size:16px}
    img{max-width:100%;height:auto;display:block}
    a{color:var(--accent);text-decoration:none}
    .lm-topbar{background:#1a1a1a;border-bottom:1px solid #333}
    .lm-topbar-container{max-width:1200px;margin:0 auto;padding:10px 20px;display:flex;justify-content:space-between;align-items:center}
    .lm-topbar-left,.lm-topbar-right{flex:1}
    .lm-topbar-right{text-align:right}
    .lm-topbar-center{flex:2;text-align:center}
    .lm-topbar-date{font-family:var(--font-sans);font-size:.75rem;color:#999;text-transform:uppercase;letter-spacing:1.5px}
    .lm-topbar-link{font-family:var(--font-sans);font-size:.75rem;color:#999;text-decoration:none;text-transform:uppercase;letter-spacing:.5px}
    .lm-header{background:#fff;border-bottom:1px solid #e5e5e5}
    .lm-header-container{max-width:1200px;margin:0 auto;padding:35px 20px 30px;text-align:center}
    .lm-logo-link{text-decoration:none;display:block}
    .lm-logo-text{font-family:var(--font-serif);font-size:3.2rem;font-weight:800;color:#1a1a1a;letter-spacing:-1px;line-height:1;display:block}
    .lm-tagline{font-family:var(--font-sans);font-size:.85rem;color:#666;margin:12px 0 0;letter-spacing:3px;text-transform:uppercase}
    .lm-nav{background:#fff;border-bottom:3px solid #1a1a1a;position:sticky;top:0;z-index:1000}
    .lm-nav-container{max-width:1200px;margin:0 auto;padding:0 20px;display:flex;align-items:center;justify-content:center}
    .lm-nav-list{list-style:none;display:flex;align-items:center;justify-content:center;gap:0;margin:0;padding:0;overflow-x:auto;scrollbar-width:none;-ms-overflow-style:none;scroll-behavior:smooth}
    .lm-nav-list::-webkit-scrollbar{display:none}
    .lm-nav-item{flex-shrink:0}
    .lm-nav-link{display:block;padding:18px 16px;font-family:var(--font-sans);font-size:.75rem;font-weight:600;color:#1a1a1a;text-decoration:none;text-transform:uppercase;letter-spacing:1px;white-space:nowrap}
    .lm-nav-toggle{display:none}
    .site-content{flex:1;padding:0}
    .lm-home{background:#fff;min-height:100vh}
    .lm-container{max-width:1200px;margin:0 auto;padding:0 20px}
    .lm-hero{padding:30px 0 40px;border-bottom:1px solid #e5e5e5}
    .lm-hero-grid{display:grid;grid-template-columns:1fr 380px;gap:30px}
    .lm-main-article{position:relative}
    .lm-main-link{display:block;text-decoration:none;color:inherit}
    .lm-main-figure{margin:0;overflow:hidden;aspect-ratio:5/3}
    .lm-main-image{width:100%;height:450px;aspect-ratio:5/3;object-fit:cover}
    .lm-secondary-articles{display:flex;flex-direction:column;gap:0}
    .lm-secondary-article{padding:20px 0;border-bottom:1px solid #e5e5e5}
    .lm-secondary-article:first-child{padding-top:0}
    .lm-secondary-article:last-child{border-bottom:none}
    .lm-secondary-featured .lm-secondary-figure{margin:0 0 12px;overflow:hidden;aspect-ratio:5/3}
    .lm-secondary-content{padding:0}
    .lm-secondary-image{width:100%;height:200px;aspect-ratio:5/3;object-fit:cover}
    .lm-latest{padding:50px 0 60px;background:#fafafa}
    .lm-latest-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:30px}
    .lm-latest-article{background:#fff;border-radius:4px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.06)}
    .lm-latest-figure{margin:0;overflow:hidden;aspect-ratio:5/3}
    .lm-latest-image{width:100%;height:200px;aspect-ratio:5/3;object-fit:cover}
    .article-detail{max-width:900px;margin:0 auto;padding:4rem 1.5rem}
    .article-header{margin-bottom:4rem}
    .article-meta{display:flex;flex-wrap:wrap;gap:2rem;align-items:center;padding:1.5rem;background:#f5f3f0;border-radius:4px;border-left:4px solid var(--accent)}
    .article-detail-image{width:100%;height:auto;aspect-ratio:5/3;max-height:600px;object-fit:cover}
    .lm-main-content{padding:20px 0}
    .lm-category{display:inline-block;font-family:var(--font-sans);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;color:var(--accent);margin-bottom:10px}
    .lm-main-title{font-family:var(--font-serif);font-size:2.4rem;font-weight:700;line-height:1.15;color:#1a1a1a;margin:0 0 15px;letter-spacing:-.5px}
    .lm-main-excerpt{font-family:var(--font-body);font-size:1.1rem;line-height:1.6;color:#4a4a4a;margin:0}
    .lm-footer-main{background:#000;color:#fff;padding:60px 0 40px;border-top:4px solid var(--accent)}
    .lm-footer-container{max-width:1200px;margin:0 auto;padding:0 20px}
    .lm-footer-brand{text-align:center;margin-bottom:50px;padding-bottom:40px;border-bottom:1px solid #333}
    .lm-footer-logo-text{font-family:var(--font-serif);font-size:2.8rem;font-weight:800;color:#fff;letter-spacing:-.8px;line-height:1}
    .lm-footer-bottom{background:#000;border-top:1px solid #333;padding:25px 0}
    .lm-footer-bottom-container{max-width:1200px;margin:0 auto;padding:0 20px;display:flex;justify-content:center;align-items:center}
    .lm-footer-copyright{font-family:var(--font-sans);font-size:.8rem;color:#999;margin:0;letter-spacing:.5px}
    @media(max-width:860px){.lm-hero-grid{grid-template-columns:1fr}.lm-main-image{height:300px}}
    @media(max-width:900px){.article-detail{padding:3rem 1.25rem}.article-header{margin-bottom:3rem}.article-meta{gap:1rem;padding:1rem}}
    @media(max-width:768px){.lm-topbar-left,.lm-topbar-right{display:none}.lm-topbar-center{flex:1}.lm-logo-text{font-size:1.8rem}.lm-nav{position:relative}.lm-nav-container{position:relative;justify-content:space-between;padding:0 60px 0 20px;min-height:48px}.lm-nav-toggle{display:flex;flex-direction:column;justify-content:center;align-items:center;width:44px;height:44px;background:0 0;border:none;cursor:pointer;padding:10px;gap:5px;position:absolute;right:8px;top:50%;transform:translateY(-50%);z-index:1001}.lm-nav-toggle-bar{width:24px;height:2px;background:#1a1a1a;transition:all .3s ease}.lm-nav-toggle[aria-expanded="true"] .lm-nav-toggle-bar:nth-child(1){transform:rotate(45deg) translate(5px,5px)}.lm-nav-toggle[aria-expanded="true"] .lm-nav-toggle-bar:nth-child(2){opacity:0}.lm-nav-toggle[aria-expanded="true"] .lm-nav-toggle-bar:nth-child(3){transform:rotate(-45deg) translate(6px,-6px)}.lm-nav-list{display:none}.lm-nav-list.is-open{display:flex;position:absolute;top:100%;left:0;right:0;background:#fff;flex-direction:column;border-bottom:3px solid #1a1a1a;box-shadow:0 4px 6px rgba(0,0,0,.1);z-index:1000;max-height:calc(100vh - 200px);overflow-y:auto}.lm-nav-list.is-open .lm-nav-item{width:100%;border-bottom:1px solid #e5e5e5}.lm-nav-list.is-open .lm-nav-item:last-child{border-bottom:none}.lm-nav-list.is-open .lm-nav-link{padding:16px 20px;text-align:left;width:100%}.lm-footer-logo-text{font-size:2rem}}
    </style>

    <!-- Load full CSS asynchronously -->
    <link rel="preload" href="/assets/css/style.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="/assets/css/article.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="/assets/css/style.css">
        <link rel="stylesheet" href="/assets/css/article.css">
    </noscript>

    <!-- Fonts loaded early with optional swap behavior to reduce late reflow -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Inter:wght@400;600;700&display=optional">
    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Inter:wght@400;600;700&display=optional">
    </noscript>
    
    <!-- Schema.org Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "NewsMediaOrganization",
        "name": "Le Journal - Couverture Iran-Irak",
        "description": "<?= $escape($metaDescription) ?>",
        "url": "<?= $escape($scheme . '://' . $host) ?>",
        "logo": "<?= $escape($scheme . '://' . $host . '/assets/images/logo.jpg') ?>",
        "sameAs": [],
        "contactPoint": {
            "@type": "ContactPoint",
            "contactType": "Editorial",
            "url": "<?= $escape($scheme . '://' . $host) ?>"
        }
    }
    </script>
</head>
<body class="newspaper-layout">
    
    <!-- Barre supérieure (date) -->
    <div class="lm-topbar" role="contentinfo">
        <div class="lm-topbar-container">
            <div class="lm-topbar-center">
                <time class="lm-topbar-date" datetime="<?= date('Y-m-d') ?>"><?php
                    $days = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
                    $months = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];

                    $now = new DateTime();
                    $dayName = $days[$now->format('N') - 1];
                    $day = $now->format('d');
                    $monthName = $months[(int)$now->format('m') - 1];
                    $year = $now->format('Y');

                    echo ucfirst($dayName) . ' ' . $day . ' ' . $monthName . ' ' . $year;
                ?></time>
            </div>
        </div>
    </div>

    <!-- Header principal -->
    <header class="lm-header" role="banner">
        <div class="lm-header-container">
            <!-- Logo et titre principal -->
            <div class="lm-header-brand">
                <a href="/" class="lm-logo-link" title="Accueil">
                    <span class="lm-logo-text">GUERRE IRAN-IRAK</span>
                </a>
                <p class="lm-tagline">Couverture complète du conflit</p>
            </div>
        </div>

        <!-- Navigation principale -->
        <nav class="lm-nav" aria-label="Menu de navigation principal">
            <div class="lm-nav-container">
                <button class="lm-nav-toggle" aria-expanded="false" aria-controls="main-menu" aria-label="Ouvrir le menu">
                    <span class="lm-nav-toggle-bar"></span>
                    <span class="lm-nav-toggle-bar"></span>
                    <span class="lm-nav-toggle-bar"></span>
                </button>
                <ul class="lm-nav-list" id="main-menu">
                    <li class="lm-nav-item lm-nav-home"><a href="/" class="lm-nav-link">Accueil</a></li>
                    <?php foreach ($navbarCategories as $navCategory): ?>
                        <li class="lm-nav-item">
                            <a href="/categorie/<?= $escape($navCategory['slug']) ?>" class="lm-nav-link" title="<?= $escape($navCategory['article_count'] ?? 0) ?> article(s)">
                                <?= $escape($navCategory['name']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </nav>
    </header>

    <main class="site-content" id="main-content" role="main">
        <?= $content ?>
    </main>

    <!-- Footer - Style Le Monde noir -->
    <footer class="lm-footer" role="contentinfo">
        <!-- Section principale du footer -->
        <div class="lm-footer-main">
            <div class="lm-footer-container">
                <!-- Logo et description centré -->
                <div class="lm-footer-brand">
                    <div class="lm-footer-logo">
                        <span class="lm-footer-logo-text">GUERRE IRAN-IRAK</span>
                    </div>
                    <p class="lm-footer-tagline">Couverture complète du conflit</p>
                    <p class="lm-footer-description">
                        Publication d'actualités professionnelle dédiée à la couverture complète du conflit Iran-Irak.
                        Analyses spécialisées, reportages vérifiés et documentation historique de référence.
                    </p>
                </div>

                <!-- Sections d'information -->
                <div class="lm-footer-sections">
                    <div class="lm-footer-section">
                        <h3 class="lm-footer-section-title">Archives</h3>
                        <p class="lm-footer-section-text">
                            Documentation complète du conflit avec analyses chronologiques et témoignages historiques.
                        </p>
                    </div>

                    <div class="lm-footer-section">
                        <h3 class="lm-footer-section-title">Journalisme</h3>
                        <p class="lm-footer-section-text">
                            Couverture professionnelle respectant les standards du journalisme d'investigation international.
                        </p>
                    </div>

                    <div class="lm-footer-section">
                        <h3 class="lm-footer-section-title">Documentation</h3>
                        <p class="lm-footer-section-text">
                            Sources vérifiées et analyse factuelle pour une compréhension approfondie des événements.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer bottom -->
        <div class="lm-footer-bottom">
            <div class="lm-footer-bottom-container">
                <div class="lm-footer-bottom-center">
                    <p class="lm-footer-copyright">
                        &copy; <?= date('Y') ?> Le Journal - Guerre Iran-Irak. Publication d'information historique.
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script>
    (function() {
        const toggle = document.querySelector('.lm-nav-toggle');
        const menu = document.getElementById('main-menu');

        if (toggle && menu) {
            toggle.addEventListener('click', function() {
                const isOpen = this.getAttribute('aria-expanded') === 'true';
                this.setAttribute('aria-expanded', !isOpen);
                menu.classList.toggle('is-open');
            });

            document.addEventListener('click', function(e) {
                if (!toggle.contains(e.target) && !menu.contains(e.target)) {
                    toggle.setAttribute('aria-expanded', 'false');
                    menu.classList.remove('is-open');
                }
            });
        }

        // Améliore le scroll horizontal du menu sur desktop
        if (menu && window.innerWidth > 768) {
            // Scroll avec la molette de souris
            menu.addEventListener('wheel', function(e) {
                if (Math.abs(e.deltaY) > Math.abs(e.deltaX)) {
                    e.preventDefault();
                    this.scrollLeft += e.deltaY;
                }
            }, { passive: false });

            // Drag to scroll
            let isDown = false;
            let startX;
            let scrollLeft;

            menu.addEventListener('mousedown', function(e) {
                isDown = true;
                menu.style.cursor = 'grabbing';
                startX = e.pageX - menu.offsetLeft;
                scrollLeft = menu.scrollLeft;
            });

            menu.addEventListener('mouseleave', function() {
                isDown = false;
                menu.style.cursor = 'grab';
            });

            menu.addEventListener('mouseup', function() {
                isDown = false;
                menu.style.cursor = 'grab';
            });

            menu.addEventListener('mousemove', function(e) {
                if (!isDown) return;
                e.preventDefault();
                const x = e.pageX - menu.offsetLeft;
                const walk = (x - startX) * 2;
                menu.scrollLeft = scrollLeft - walk;
            });

            // Scroll fluide avec les touches fléchées
            menu.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    this.scrollBy({ left: -100, behavior: 'smooth' });
                } else if (e.key === 'ArrowRight') {
                    e.preventDefault();
                    this.scrollBy({ left: 100, behavior: 'smooth' });
                }
            });

            // Style du curseur pour indiquer le drag
            menu.style.cursor = 'grab';
        }
    })();
    </script>
</body>
</html>
