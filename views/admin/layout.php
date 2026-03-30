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
                <div class="admin-sidebar-header">
                    <div class="admin-sidebar-logo">
                        <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect width="32" height="32" rx="8" fill="#BC0000"/>
                            <path d="M8 16L14 10L14 22L8 16Z" fill="white"/>
                            <path d="M18 10L24 16L18 22L18 10Z" fill="white" opacity="0.7"/>
                        </svg>
                        <span>Administration</span>
                    </div>
                </div>
                <nav class="admin-sidebar-nav">
                    <a class="admin-sidebar-link<?= $isActivePath('/admin/dashboard') ? ' active' : '' ?>" href="/admin/dashboard">
                        <svg class="admin-sidebar-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                        <span>Dashboard</span>
                    </a>

                    <div class="admin-sidebar-group">
                        <button class="admin-sidebar-link admin-sidebar-link--parent<?= $isActivePath('/admin/articles') ? ' active' : '' ?>" data-submenu-toggle="articles">
                            <div class="admin-sidebar-link-content">
                                <svg class="admin-sidebar-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                    <line x1="10" y1="9" x2="8" y2="9"></line>
                                </svg>
                                <span>Articles</span>
                            </div>
                            <svg class="admin-sidebar-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <div class="admin-sidebar-submenu" data-submenu="articles">
                            <a class="admin-sidebar-sublink<?= $currentPath === '/admin/articles/create' ? ' active' : '' ?>" href="/admin/articles/create">Ajouter</a>
                            <a class="admin-sidebar-sublink<?= $currentPath === '/admin/articles' ? ' active' : '' ?>" href="/admin/articles">Liste</a>
                        </div>
                    </div>

                    <div class="admin-sidebar-group">
                        <button class="admin-sidebar-link admin-sidebar-link--parent<?= $isActivePath('/admin/categories') ? ' active' : '' ?>" data-submenu-toggle="categories">
                            <div class="admin-sidebar-link-content">
                                <svg class="admin-sidebar-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z"></path>
                                </svg>
                                <span>Catégories</span>
                            </div>
                            <svg class="admin-sidebar-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <div class="admin-sidebar-submenu" data-submenu="categories">
                            <a class="admin-sidebar-sublink<?= $currentPath === '/admin/categories/create' ? ' active' : '' ?>" href="/admin/categories/create">Ajouter</a>
                            <a class="admin-sidebar-sublink<?= $currentPath === '/admin/categories' ? ' active' : '' ?>" href="/admin/categories">Liste</a>
                        </div>
                    </div>

                    <a class="admin-sidebar-link<?= $isActivePath('/admin/users') ? ' active' : '' ?>" href="/admin/users">
                        <svg class="admin-sidebar-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span>Users</span>
                    </a>
                </nav>

                <div class="admin-sidebar-footer">
                    <button type="button" class="btn-sidebar-toggle" data-admin-sidebar-toggle aria-controls="admin-sidebar" aria-expanded="false">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                        <span>Menu</span>
                    </button>

                    <?php if ($authUser !== null): ?>
                        <div class="admin-sidebar-user">
                            <svg class="admin-sidebar-user-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <div class="admin-sidebar-user-info">
                                <div class="admin-sidebar-user-name"><?= $escape((string) ($authUser['username'] ?? '')) ?></div>
                                <div class="admin-sidebar-user-role"><?= $escape((string) ($authUser['role'] ?? '')) ?></div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form class="logout-form" method="post" action="/logout">
                        <input type="hidden" name="_token" value="<?= $escape($csrfToken) ?>">
                        <button class="btn btn-outline btn-logout" type="submit">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <polyline points="16 17 21 12 16 7"></polyline>
                                <line x1="21" y1="12" x2="9" y2="12"></line>
                            </svg>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </aside>

            <div class="admin-main">
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
