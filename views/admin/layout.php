<?php

declare(strict_types=1);

$title = isset($title) && is_string($title) && $title !== '' ? $title : 'Administration';
$showAdminNav = isset($showAdminNav) ? (bool) $showAdminNav : false;
$authUser = isset($authUser) && is_array($authUser) ? $authUser : null;
$csrfToken = isset($csrfToken) && is_string($csrfToken) ? $csrfToken : '';
$content = isset($content) && is_string($content) ? $content : '';

$escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $escape($title) ?></title>
    <style>
        body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; margin: 0; color: #111827; background: #f3f4f6; }
        .page { max-width: 980px; margin: 0 auto; padding: 20px; }
        .panel { background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 18px; }
        .admin-nav { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; }
        .admin-nav a { color: #0f172a; text-decoration: none; padding: 6px 10px; border: 1px solid #d1d5db; border-radius: 6px; background: #ffffff; }
        .admin-nav a:hover { background: #f9fafb; }
        .logout-form { display: inline; margin: 0; }
        .logout-button { border: 1px solid #d1d5db; background: #ffffff; color: #991b1b; border-radius: 6px; padding: 6px 10px; cursor: pointer; }
        .meta { margin-bottom: 14px; color: #4b5563; font-size: 0.95rem; }
    </style>
</head>
<body>
    <main class="page">
        <?php if ($showAdminNav): ?>
            <nav class="admin-nav" aria-label="Navigation administration">
                <a href="/admin/dashboard">Dashboard</a>
                <a href="/admin/articles">Articles</a>
                <a href="/admin/categories">Categories</a>
                <a href="/admin/users">Users</a>
                <form class="logout-form" method="post" action="/logout">
                    <input type="hidden" name="_token" value="<?= $escape($csrfToken) ?>">
                    <button class="logout-button" type="submit">Logout</button>
                </form>
            </nav>
            <?php if ($authUser !== null): ?>
                <p class="meta">
                    Connecte en tant que <strong><?= $escape((string) ($authUser['username'] ?? '')) ?></strong>
                    (role: <?= $escape((string) ($authUser['role'] ?? '')) ?>)
                </p>
            <?php endif; ?>
        <?php endif; ?>

        <section class="panel">
            <?= $content ?>
        </section>
    </main>
</body>
</html>
