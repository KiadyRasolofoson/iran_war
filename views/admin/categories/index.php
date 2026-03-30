<?php

declare(strict_types=1);

$categories = is_array($categories ?? null) ? $categories : [];
$flashSuccess = $flashSuccess ?? null;
$flashError = $flashError ?? null;
$csrfToken = (string) ($csrfToken ?? '');

$h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>

<div class="d-flex align-items-center justify-content-between mb-3" style="flex-wrap: wrap;">
    <h1 class="mb-1" style="margin: 0;">Gestion des categories</h1>
    <a class="btn btn-primary" href="/admin/categories/create">Creer une categorie</a>
</div>

<?php if (is_string($flashSuccess) && $flashSuccess !== ''): ?>
    <div class="alert alert-info alert-success"><?= $h($flashSuccess) ?></div>
<?php endif; ?>

<?php if (is_string($flashError) && $flashError !== ''): ?>
    <div class="alert alert-info alert-error"><?= $h($flashError) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">Liste des categories</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
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
                        <td class="text-center" colspan="5">Aucune categorie.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categories as $category): ?>
                        <?php
                        $categoryId = (int) ($category['id'] ?? 0);
                        $categoryStatus = strtolower((string) ($category['status'] ?? ''));
                        $statusClass = $categoryStatus === 'active' ? 'badge-primary' : 'badge-neutral';
                        ?>
                        <tr>
                            <td><?= $categoryId ?></td>
                            <td><?= $h((string) ($category['name'] ?? '')) ?></td>
                            <td><?= $h((string) ($category['slug'] ?? '')) ?></td>
                            <td>
                                <span class="badge <?= $statusClass ?>"><?= $h((string) ($category['status'] ?? '')) ?></span>
                            </td>
                            <td>
                                <div class="d-flex gap-2" style="flex-wrap: wrap;">
                                    <a class="btn btn-outline" href="/admin/categories/<?= $categoryId ?>/edit">Modifier</a>
                                    <form method="post" action="/admin/categories/<?= $categoryId ?>/delete" onsubmit="return confirm('Supprimer cette categorie ?');" style="margin: 0;">
                                        <input type="hidden" name="_token" value="<?= $h($csrfToken) ?>">
                                        <button class="btn btn-outline" type="submit">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            </table>
        </div>
    </div>
</div>
