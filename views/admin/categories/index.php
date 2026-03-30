<?php

declare(strict_types=1);

$categories = is_array($categories ?? null) ? $categories : [];
$flashSuccess = $flashSuccess ?? null;
$flashError = $flashError ?? null;
$csrfToken = (string) ($csrfToken ?? '');

$h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<style>
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 0.6rem; text-align: left; }
    .actions { display: flex; gap: 0.5rem; }
    .flash-success { color: #0b6b2a; }
    .flash-error { color: #8a1f11; }
    .btn { display: inline-block; border: 1px solid #333; padding: 8px 12px; border-radius: 6px; background: #fff; color: #111; text-decoration: none; }
</style>

<h1>Gestion des categories</h1>
<p><a class="btn" href="/admin/categories/create">Creer une categorie</a></p>

<?php if (is_string($flashSuccess) && $flashSuccess !== ''): ?>
    <p class="flash-success"><?= $h($flashSuccess) ?></p>
<?php endif; ?>

<?php if (is_string($flashError) && $flashError !== ''): ?>
    <p class="flash-error"><?= $h($flashError) ?></p>
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
                    <td><?= $h((string) ($category['name'] ?? '')) ?></td>
                    <td><?= $h((string) ($category['slug'] ?? '')) ?></td>
                    <td><?= $h((string) ($category['status'] ?? '')) ?></td>
                    <td>
                        <div class="actions">
                            <a href="/admin/categories/<?= (int) ($category['id'] ?? 0) ?>/edit">Modifier</a>
                            <form method="post" action="/admin/categories/<?= (int) ($category['id'] ?? 0) ?>/delete" onsubmit="return confirm('Supprimer cette categorie ?');">
                                <input type="hidden" name="_token" value="<?= $h($csrfToken) ?>">
                                <button type="submit">Supprimer</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
