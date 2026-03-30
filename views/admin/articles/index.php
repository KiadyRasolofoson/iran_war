<?php

declare(strict_types=1);

$articles = is_array($articles ?? null) ? $articles : [];
$pagination = is_array($pagination ?? null) ? $pagination : [];
$filters = is_array($filters ?? null) ? $filters : [];
$categories = is_array($categories ?? null) ? $categories : [];
$flash = is_array($flash ?? null) ? $flash : [];
$csrfToken = (string) ($csrfToken ?? '');

$h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$statusValue = (string) ($filters['status'] ?? '');
$categoryValue = (string) ($filters['category_id'] ?? '');
$searchValue = (string) ($filters['q'] ?? '');

$currentPage = (int) ($pagination['page'] ?? 1);
$totalPages = (int) ($pagination['total_pages'] ?? 1);

$queryBase = [
    'q' => $searchValue,
    'status' => $statusValue,
    'category_id' => $categoryValue,
];
?>

<div style="display: flex; gap: 12px; align-items: center; justify-content: space-between; flex-wrap: wrap; margin-bottom: 24px;">
    <h1 style="margin: 0;">Administration des articles</h1>
    <a class="btn btn-primary" href="/admin/articles/create">Nouvel article</a>
</div>

<?php foreach ($flash as $message): ?>
    <?php
    $typeClass = 'alert-' . $h((string) ($message['type'] ?? ''));
    if ($typeClass === 'alert-error') {
        $typeClass = 'alert-error';
    } elseif ($typeClass === 'alert-success') {
        $typeClass = 'alert-success';
    } else {
        $typeClass = 'alert-info';
    }
    ?>
    <div class="alert <?= $typeClass ?>">
        <?= $h((string) ($message['message'] ?? '')) ?>
    </div>
<?php endforeach; ?>

<div class="card mb-3">
    <div class="card-header">Filtres</div>
    <div class="card-body">
        <form method="get" action="/admin/articles" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px;">
            <div class="form-group" style="margin: 0;">
                <label class="form-label" for="q">Recherche</label>
                <input class="form-control" type="text" id="q" name="q" value="<?= $h($searchValue) ?>" placeholder="Titre ou contenu">
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" for="status">Statut</label>
                <select class="form-control" id="status" name="status">
                    <option value="">Tous</option>
                    <option value="draft" <?= $statusValue === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= $statusValue === 'published' ? 'selected' : '' ?>>Published</option>
                </select>
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label" for="category_id">Categorie</label>
                <select class="form-control" id="category_id" name="category_id">
                    <option value="">Toutes</option>
                    <?php foreach ($categories as $category): ?>
                        <?php $id = (string) ($category['id'] ?? ''); ?>
                        <option value="<?= $h($id) ?>" <?= $categoryValue === $id ? 'selected' : '' ?>>
                            <?= $h((string) ($category['name'] ?? '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="align-self: end; padding-bottom: 2px;">
                <button class="btn btn-primary" type="submit">Filtrer</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Liste des articles</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Titre</th>
                    <th>Slug</th>
                    <th>Statut</th>
                    <th>Categorie</th>
                    <th>Publication</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if ($articles === []): ?>
                    <tr>
                        <td class="text-center" colspan="7">Aucun article trouve.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($articles as $article): ?>
                        <?php
                        $articleId = (int) ($article['id'] ?? 0);
                        $articleStatus = (string) ($article['status'] ?? 'draft');
                        ?>
                        <tr>
                            <td><?= $h($articleId) ?></td>
                            <td><?= $h((string) ($article['title'] ?? '')) ?></td>
                            <td><code><?= $h((string) ($article['slug'] ?? '')) ?></code></td>
                            <td>
                                <span class="badge <?= $articleStatus === 'published' ? 'badge-primary' : 'badge-neutral' ?>">
                                    <?= $h($articleStatus) ?>
                                </span>
                            </td>
                            <td><?= $h((string) ($article['category_name'] ?? '-')) ?></td>
                            <td><?= $h((string) ($article['published_at'] ?? '-')) ?></td>
                            <td>
                                <div class="d-flex gap-2" style="flex-wrap: wrap;">
                                    <a class="btn btn-outline" href="/admin/articles/<?= $h($articleId) ?>/edit">Modifier</a>

                                    <form method="post" action="/admin/articles/<?= $h($articleId) ?>/toggle-status" style="margin: 0;">
                                        <input type="hidden" name="_token" value="<?= $h($csrfToken) ?>">
                                        <button class="btn btn-outline" type="submit">
                                            <?= $articleStatus === 'published' ? 'Depublier' : 'Publier' ?>
                                        </button>
                                    </form>

                                    <form method="post" action="/admin/articles/<?= $h($articleId) ?>/delete" onsubmit="return confirm('Supprimer cet article ?');" style="margin: 0;">
                                        <input type="hidden" name="_token" value="<?= $h($csrfToken) ?>">
                                        <button class="btn btn-outline" style="color: var(--color-accent); border-color: var(--color-accent);" type="submit">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <ul class="pagination" style="justify-content: center; align-items: center; flex-wrap: wrap;">
            <li class="page-item <?= ($pagination['has_prev'] ?? false) === true ? '' : 'disabled' ?>">
                <?php if (($pagination['has_prev'] ?? false) === true): ?>
                    <?php $prevQuery = http_build_query(array_merge($queryBase, ['page' => $currentPage - 1])); ?>
                    <a class="page-link" href="/admin/articles?<?= $h($prevQuery) ?>">Precedent</a>
                <?php else: ?>
                    <span class="page-link">Precedent</span>
                <?php endif; ?>
            </li>

            <li class="page-item active" aria-current="page">
                <span class="page-link">Page <?= $h($currentPage) ?> / <?= $h($totalPages) ?></span>
            </li>

            <li class="page-item <?= ($pagination['has_next'] ?? false) === true ? '' : 'disabled' ?>">
                <?php if (($pagination['has_next'] ?? false) === true): ?>
                    <?php $nextQuery = http_build_query(array_merge($queryBase, ['page' => $currentPage + 1])); ?>
                    <a class="page-link" href="/admin/articles?<?= $h($nextQuery) ?>">Suivant</a>
                <?php else: ?>
                    <span class="page-link">Suivant</span>
                <?php endif; ?>
            </li>
        </ul>
    </div>
</div>
