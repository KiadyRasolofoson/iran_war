<?php

declare(strict_types=1);

$categories = is_array($categories ?? null) ? $categories : [];
$flashSuccess = $flashSuccess ?? null;
$flashError = $flashError ?? null;
$csrfToken = (string) ($csrfToken ?? '');

?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Categories</title>
    <style>
        body { font-family: sans-serif; margin: 2rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 0.6rem; text-align: left; }
        .actions { display: flex; gap: 0.5rem; }
        .flash-success { color: #0b6b2a; }
        .flash-error { color: #8a1f11; }
    </style>
</head>
<body>
    <h1>Gestion des categories</h1>

    <p><a href="/admin/categories/create">Creer une categorie</a></p>

    <?php if (is_string($flashSuccess) && $flashSuccess !== ''): ?>
        <p class="flash-success"><?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (is_string($flashError) && $flashError !== ''): ?>
        <p class="flash-error"><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Slug</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($categories === []): ?>
                <tr>
                    <td colspan="5">Aucune categorie.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?= (int) ($category['id'] ?? 0) ?></td>
                        <td><?= htmlspecialchars((string) ($category['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($category['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($category['status'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <div class="actions">
                                <a href="/admin/categories/<?= (int) ($category['id'] ?? 0) ?>/edit">Modifier</a>
                                <form method="post" action="/admin/categories/<?= (int) ($category['id'] ?? 0) ?>/delete" onsubmit="return confirm('Supprimer cette categorie ?');">
                                    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                    <button type="submit">Supprimer</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
