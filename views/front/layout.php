<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Titre du Site') ?></title>
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="<?= htmlspecialchars($metaDescription ?? 'Description par défaut du site.') ?>">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle ?? 'Titre du Site') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($metaDescription ?? 'Description par défaut du site.') ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= htmlspecialchars('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($ogImage ?? '/assets/images/default-og.jpg') ?>">
    
    <!-- Style -->
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <header class="site-header">
        <div class="container header-container">
            <div class="logo">
                <a href="/">InfoPortail</a>
            </div>
            <nav class="main-nav" aria-label="Menu principal">
                <ul>
                    <li><a href="/">Accueil</a></li>
                    <li><a href="/articles">Articles</a></li>
                    <li><a href="/a-propos">A propos</a></li>
                    <li><a href="/login" class="nav-btn">Connexion</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="site-content">
        <?php if (isset($view) && file_exists($view)) {
            require $view;
        } else {
            echo "<p>Vue introuvable.</p>";
        } ?>
    </main>

    <footer class="site-footer">
        <div class="container footer-container">
            <p>&copy; <?= date('Y') ?> InfoPortail. Tous droits réservés.</p>
            <nav class="footer-nav" aria-label="Menu de pied de page">
                <ul>
                    <li><a href="/a-propos">A propos</a></li>
                    <li><a href="/articles">Articles</a></li>
                </ul>
            </nav>
        </div>
    </footer>
</body>
</html>
