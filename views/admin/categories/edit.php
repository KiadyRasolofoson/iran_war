<?php

declare(strict_types=1);

$errors = is_array($errors ?? null) ? $errors : [];
$category = is_array($category ?? null) ? $category : [];
$csrfToken = (string) ($csrfToken ?? '');

$h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<div class="d-flex justify-content-between align-items-center mb-2">
    <h1 class="mb-1">Modifier une categorie</h1>
    <a class="btn btn-outline" href="/admin/categories">Retour a la liste</a>
</div>

<?php if ($errors !== []): ?>
    <div class="alert alert-info" role="alert" aria-live="assertive" id="category-form-errors">
        <strong>Veuillez corriger les erreurs suivantes :</strong>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= $h((string) $error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" action="/admin/categories/<?= (int) ($category['id'] ?? 0) ?>/update" class="card" <?= $errors !== [] ? 'aria-describedby="category-form-errors"' : '' ?>>
    <div class="card-header">Informations categorie</div>
    <div class="card-body">
        <input type="hidden" name="_token" value="<?= $h($csrfToken) ?>">

        <div class="form-group">
            <label class="form-label" for="name">Nom</label>
            <input class="form-control" id="name" name="name" type="text" required value="<?= $h((string) ($category['name'] ?? '')) ?>">
        </div>

        <div class="form-group">
            <label class="form-label" for="slug">Slug</label>
            <input class="form-control" id="slug" name="slug" type="text" value="<?= $h((string) ($category['slug'] ?? '')) ?>">
        </div>

        <div class="form-group">
            <label class="form-label" for="description">Description</label>
            <textarea class="form-control" id="description" name="description" rows="4"><?= $h((string) ($category['description'] ?? '')) ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label" for="seo_title">SEO title</label>
            <input class="form-control" id="seo_title" name="seo_title" type="text" value="<?= $h((string) ($category['seo_title'] ?? '')) ?>">
        </div>

        <div class="form-group">
            <label class="form-label" for="seo_description">SEO description</label>
            <textarea class="form-control" id="seo_description" name="seo_description" rows="3"><?= $h((string) ($category['seo_description'] ?? '')) ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label" for="status">Statut</label>
            <select class="form-control" id="status" name="status">
                <option value="active" <?= (($category['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>active</option>
                <option value="hidden" <?= (($category['status'] ?? '') === 'hidden') ? 'selected' : '' ?>>hidden</option>
            </select>
        </div>

        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a class="btn btn-outline" href="/admin/categories">Annuler</a>
        </div>
    </div>
</form>
