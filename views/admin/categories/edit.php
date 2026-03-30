<?php

declare(strict_types=1);

$errors = is_array($errors ?? null) ? $errors : [];
$category = is_array($category ?? null) ? $category : [];
$csrfToken = (string) ($csrfToken ?? '');

$h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<style>
    label { display: block; margin-top: 0.8rem; }
    input, textarea, select { width: 100%; padding: 0.5rem; }
    .errors { color: #8a1f11; }
    .actions { margin-top: 1rem; display: flex; gap: 0.5rem; }
    .btn { display: inline-block; border: 1px solid #333; padding: 8px 12px; border-radius: 6px; background: #fff; color: #111; text-decoration: none; }
</style>

<h1>Modifier une categorie</h1>
<p><a class="btn" href="/admin/categories">Retour a la liste</a></p>

<?php if ($errors !== []): ?>
    <div class="errors">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= $h((string) $error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" action="/admin/categories/<?= (int) ($category['id'] ?? 0) ?>/update">
    <input type="hidden" name="_token" value="<?= $h($csrfToken) ?>">

    <label for="name">Nom</label>
    <input id="name" name="name" type="text" required value="<?= $h((string) ($category['name'] ?? '')) ?>">

    <label for="slug">Slug</label>
    <input id="slug" name="slug" type="text" value="<?= $h((string) ($category['slug'] ?? '')) ?>">

    <label for="description">Description</label>
    <textarea id="description" name="description" rows="4"><?= $h((string) ($category['description'] ?? '')) ?></textarea>

    <label for="seo_title">SEO title</label>
    <input id="seo_title" name="seo_title" type="text" value="<?= $h((string) ($category['seo_title'] ?? '')) ?>">

    <label for="seo_description">SEO description</label>
    <textarea id="seo_description" name="seo_description" rows="3"><?= $h((string) ($category['seo_description'] ?? '')) ?></textarea>

    <label for="status">Statut</label>
    <select id="status" name="status">
        <option value="active" <?= (($category['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>active</option>
        <option value="hidden" <?= (($category['status'] ?? '') === 'hidden') ? 'selected' : '' ?>>hidden</option>
    </select>

    <div class="actions">
        <button type="submit">Enregistrer</button>
    </div>
</form>
