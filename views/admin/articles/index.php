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
<style>
    .topbar { display: flex; gap: 12px; align-items: center; justify-content: space-between; flex-wrap: wrap; }
    .btn { display: inline-block; padding: 8px 12px; border-radius: 6px; border: 1px solid #333; text-decoration: none; color: #111; background: #fff; }
    .btn.primary { background: #111; color: #fff; }
    .btn.warn { border-color: #9a6b00; color: #9a6b00; }
    .btn.danger { border-color: #a61212; color: #a61212; }
    .filters { margin: 16px 0; padding: 12px; border: 1px solid #ddd; border-radius: 8px; display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 8px; }
    input, select { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
    table { width: 100%; border-collapse: collapse; margin-top: 12px; }
    th, td { border-bottom: 1px solid #e8e8e8; padding: 10px 8px; vertical-align: top; text-align: left; }
    .actions { display: flex; gap: 6px; flex-wrap: wrap; }
    .badge { padding: 2px 8px; border-radius: 999px; font-size: 12px; display: inline-block; }
    .badge.draft { background: #f4f4f4; color: #444; }
    .badge.published { background: #d9f7de; color: #186b2f; }
    .flash { margin: 8px 0; padding: 10px; border-radius: 6px; }
    .flash.success { background: #e7f9ec; color: #115926; }
    .flash.error { background: #ffeaea; color: #8f1515; }
    .pagination { display: flex; gap: 10px; margin-top: 14px; align-items: center; }
    form.inline { display: inline; }
</style>

<div class="topbar">
    <h1>Administration des articles</h1>
    <a class="btn primary" href="/admin/articles/create">Nouvel article</a>
</div>

<?php foreach ($flash as $message): ?>
    <div class="flash <?= $h((string) ($message['type'] ?? '')) ?>">
        <?= $h((string) ($message['message'] ?? '')) ?>
    </div>
<?php endforeach; ?>

<form method="get" action="/admin/articles" class="filters">
    <div>
        <label for="q">Recherche</label>
        <input type="text" id="q" name="q" value="<?= $h($searchValue) ?>" placeholder="Titre ou contenu">
    </div>
    <div>
        <label for="status">Statut</label>
        <select id="status" name="status">
            <option value="">Tous</option>
            <option value="draft" <?= $statusValue === 'draft' ? 'selected' : '' ?>>Draft</option>
            <option value="published" <?= $statusValue === 'published' ? 'selected' : '' ?>>Published</option>
        </select>
    </div>
    <div>
        <label for="category_id">Categorie</label>
        <select id="category_id" name="category_id">
            <option value="">Toutes</option>
            <?php foreach ($categories as $category): ?>
                <?php $id = (string) ($category['id'] ?? ''); ?>
                <option value="<?= $h($id) ?>" <?= $categoryValue === $id ? 'selected' : '' ?>>
                    <?= $h((string) ($category['name'] ?? '')) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div style="align-self: end;">
        <button class="btn" type="submit">Filtrer</button>
    </div>
</form>

<table>
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
            <td colspan="7">Aucun article trouve.</td>
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
                <td><span class="badge <?= $h($articleStatus) ?>"><?= $h($articleStatus) ?></span></td>
                <td><?= $h((string) ($article['category_name'] ?? '-')) ?></td>
                <td><?= $h((string) ($article['published_at'] ?? '-')) ?></td>
                <td>
                    <div class="actions">
                        <a class="btn" href="/admin/articles/<?= $h($articleId) ?>/edit">Modifier</a>

                        <form class="inline" method="post" action="/admin/articles/<?= $h($articleId) ?>/toggle-status">
                            <input type="hidden" name="_token" value="<?= $h($csrfToken) ?>">
                            <button class="btn warn" type="submit">
                                <?= $articleStatus === 'published' ? 'Depublier' : 'Publier' ?>
                            </button>
                        </form>

                        <form class="inline" method="post" action="/admin/articles/<?= $h($articleId) ?>/delete" onsubmit="return confirm('Supprimer cet article ?');">
                            <input type="hidden" name="_token" value="<?= $h($csrfToken) ?>">
                            <button class="btn danger" type="submit">Supprimer</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>

<div class="pagination">
    <?php if (($pagination['has_prev'] ?? false) === true): ?>
        <?php $prevQuery = http_build_query(array_merge($queryBase, ['page' => $currentPage - 1])); ?>
        <a class="btn" href="/admin/articles?<?= $h($prevQuery) ?>">Precedent</a>
    <?php endif; ?>

    <span>Page <?= $h($currentPage) ?> / <?= $h($totalPages) ?></span>

    <?php if (($pagination['has_next'] ?? false) === true): ?>
        <?php $nextQuery = http_build_query(array_merge($queryBase, ['page' => $currentPage + 1])); ?>
        <a class="btn" href="/admin/articles?<?= $h($nextQuery) ?>">Suivant</a>
    <?php endif; ?>
</div>
