<?php

declare(strict_types=1);

$pageTitle = isset($pageTitle) && is_string($pageTitle) && $pageTitle !== '' ? $pageTitle : 'Guerre Iran Irak';
$metaDescription = isset($metaDescription) && is_string($metaDescription) && $metaDescription !== ''
    ? $metaDescription
    : 'Actualites, analyses et dossiers sur la guerre Iran-Irak.';
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
    <title><?= $escape($pageTitle) ?></title>
    <meta name="description" content="<?= $escape($metaDescription) ?>">
    <meta name="robots" content="index, follow">
    <meta property="og:title" content="<?= $escape($pageTitle) ?>">
    <meta property="og:description" content="<?= $escape($metaDescription) ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= $escape($canonicalUrl) ?>">
    <meta property="og:image" content="<?= $escape($ogImage) ?>">
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
        <?= $content ?>
    </main>

    <footer class="site-footer">
        <div class="container footer-container">
            <p>&copy; <?= date('Y') ?> InfoPortail. Tous droits reserves.</p>
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
