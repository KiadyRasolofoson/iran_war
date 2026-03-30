<?php

declare(strict_types=1);

$title = isset($title) && is_string($title) && $title !== '' ? $title : 'Administration';
$showAdminNav = isset($showAdminNav) ? (bool) $showAdminNav : false;
$authUser = isset($authUser) && is_array($authUser) ? $authUser : null;
$csrfToken = isset($csrfToken) && is_string($csrfToken) ? $csrfToken : '';
$content = isset($content) && is_string($content) ? $content : '';
$requestUri = isset($_SERVER['REQUEST_URI']) && is_string($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
$currentPath = parse_url($requestUri, PHP_URL_PATH);
$currentPath = is_string($currentPath) && $currentPath !== '' ? $currentPath : '/';

$escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
$isActivePath = static fn(string $path) => $currentPath === $path || strpos($currentPath, $path . '/') === 0;
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $escape($title) ?></title>
    <link rel="stylesheet" href="/assets/css/admin-style.css">
</head>
<body>
    <?php if ($showAdminNav): ?>
        <div class="admin-layout" data-admin-layout>
            <aside class="admin-sidebar" id="admin-sidebar" aria-label="Navigation administration" data-admin-sidebar>
                <div class="admin-sidebar-header">Administration</div>
                <nav class="admin-sidebar-nav">
                    <a class="admin-sidebar-link<?= $isActivePath('/admin/dashboard') ? ' active' : '' ?>" href="/admin/dashboard">Dashboard</a>
                    <a class="admin-sidebar-link<?= $isActivePath('/admin/articles') ? ' active' : '' ?>" href="/admin/articles">Articles</a>
                    <a class="admin-sidebar-link<?= $isActivePath('/admin/categories') ? ' active' : '' ?>" href="/admin/categories">Categories</a>
                    <a class="admin-sidebar-link<?= $isActivePath('/admin/users') ? ' active' : '' ?>" href="/admin/users">Users</a>
                </nav>
            </aside>

            <div class="admin-main">
                <header class="admin-header">
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline" data-admin-sidebar-toggle aria-controls="admin-sidebar" aria-expanded="false">Menu</button>
                        <?php if ($authUser !== null): ?>
                            <span class="meta">
                                Connecte en tant que <strong><?= $escape((string) ($authUser['username'] ?? '')) ?></strong>
                                (role: <?= $escape((string) ($authUser['role'] ?? '')) ?>)
                            </span>
                        <?php endif; ?>
                    </div>

                    <form class="logout-form" method="post" action="/logout">
                        <input type="hidden" name="_token" value="<?= $escape($csrfToken) ?>">
                        <button class="btn btn-outline" type="submit">Logout</button>
                    </form>
                </header>

                <main class="admin-content" role="main">
                    <?= $content ?>
                </main>
            </div>
        </div>
    <?php else: ?>
        <main class="admin-content" role="main">
            <?= $content ?>
        </main>
    <?php endif; ?>

    <script src="/assets/js/admin-ui.js" defer></script>
</body>
</html>
