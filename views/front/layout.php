<?php

declare(strict_types=1);

$pageTitle = isset($pageTitle) && is_string($pageTitle) && $pageTitle !== '' ? $pageTitle : 'Actualités - Conflit Iran-Irak | Couverture Complète';
$metaDescription = isset($metaDescription) && is_string($metaDescription) && $metaDescription !== ''
    ? $metaDescription
    : 'Couverture journalistique complète du conflit Iran-Irak : analyses approfondies, dossiers spécialisés, et actualités en temps réel. Reportages professionnels et fiables.';
$ogImage = isset($ogImage) && is_string($ogImage) && $ogImage !== '' ? $ogImage : '/assets/images/default-og.jpg';
$content = isset($content) && is_string($content) ? $content : '';

$escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

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
    
    <!-- Preconnect & DNS Prefetch -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    
    <!-- Font Preload -->
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Lora:wght@400;600&family=Inter:wght@400;600;700&display=swap" as="style">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;800&family=Lora:wght@400;600&family=Inter:wght@400;600;700&display=swap">
    
    <!-- Stylesheet -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/article.css">
    <link rel="stylesheet" href="/assets/css/articles-list.css">
    
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
    <a href="#main-content" class="skip-link">Aller au contenu principal</a>
    
    <!-- Barre supérieure (date et breaking news) -->
    <div class="top-bar" role="contentinfo">
        <div class="container">
            <span class="current-date"><?php
                $days = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche'];
                $months = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
                
                $now = new DateTime();
                $dayName = $days[$now->format('N') - 1];
                $day = $now->format('d');
                $monthName = $months[(int)$now->format('m') - 1];
                $year = $now->format('Y');
                
                echo ucfirst($dayName) . ' ' . $day . ' ' . $monthName . ' ' . $year;
            ?></span>
        </div>
    </div>

    <!-- Header principal -->
    <header class="site-header" role="banner">
        <div class="container header-container">
            <div class="site-branding">
                <div class="site-logo">
                    <h1><a href="/" title="Accueil - Couverture Complète Iran-Irak">GUERRE IRAN</a></h1>
                </div>
                <p class="site-tagline">Couverture du Conflit Iran-Irak</p>
            </div>
            <nav class="main-nav" aria-label="Menu de navigation principal">
                <ul>
                    <li><a href="/" title="Page d'accueil">Accueil</a></li>
                    <li><a href="/articles" title="Tous les articles">Tous les Articles</a></li>
                    <li><a href="/a-propos" title="À propos de nous">À Propos</a></li>
                    <li><a href="/login" class="nav-btn" title="Espace rédaction">Espace Rédaction</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="site-content" id="main-content" role="main">
        <?= $content ?>
    </main>

    <footer class="site-footer" role="contentinfo">
        <div class="container footer-top">
            <div class="footer-section">
                <h3>À PROPOS</h3>
                <p>Le Journal est une publication d'actualités professionnelle dédiée à la couverture complète et rigoureuse du conflit Iran-Irak, avec analyses spécialisées et reportages vérifiés.</p>
            </div>
            <div class="footer-section">
                <h3>CATÉGORIES</h3>
                <ul>
                    <li><a href="/articles?category=politique" title="Articles de politique">Politique</a></li>
                    <li><a href="/articles?category=militaire" title="Articles militaires">Militaire</a></li>
                    <li><a href="/articles?category=economie" title="Articles d'économie">Économie</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>NAVIGATION</h3>
                <ul>
                    <li><a href="/" title="Accueil">Accueil</a></li>
                    <li><a href="/articles" title="Tous les articles">Articles</a></li>
                    <li><a href="/a-propos" title="À propos">À Propos</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> Le Journal. Tous droits réservés. | <a href="/mentions-legales">Mentions Légales</a> | <a href="/politique-confidentialite">Politique de Confidentialité</a></p>
        </div>
    </footer>
</body>
</html>
