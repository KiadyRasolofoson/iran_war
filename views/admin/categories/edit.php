<?php

declare(strict_types=1);

$errors = is_array($errors ?? null) ? $errors : [];
$category = is_array($category ?? null) ? $category : [];
$csrfToken = (string) ($csrfToken ?? '');

?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editer Categorie</title>
    <style>
        body { font-family: sans-serif; margin: 2rem; max-width: 900px; }
        label { display: block; margin-top: 0.8rem; }
        input, textarea, select { width: 100%; padding: 0.5rem; }
        .errors { color: #8a1f11; }
        .actions { margin-top: 1rem; display: flex; gap: 0.5rem; }
    </style>
</head>
<body>
    <h1>Modifier une categorie</h1>

    <p><a href="/admin/categories">Retour a la liste</a></p>

    <?php if ($errors !== []): ?>
        <div class="errors">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="/admin/categories/<?= (int) ($category['id'] ?? 0) ?>/update">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

        <label for="name">Nom</label>
        <input id="name" name="name" type="text" required value="<?= htmlspecialchars((string) ($category['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">

        <label for="slug">Slug</label>
        <input id="slug" name="slug" type="text" value="<?= htmlspecialchars((string) ($category['slug'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">

        <label for="description">Description</label>
        <textarea id="description" name="description" rows="4"><?= htmlspecialchars((string) ($category['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>

        <label for="seo_title">SEO title</label>
        <input id="seo_title" name="seo_title" type="text" value="<?= htmlspecialchars((string) ($category['seo_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">

        <label for="seo_description">SEO description</label>
        <textarea id="seo_description" name="seo_description" rows="3"><?= htmlspecialchars((string) ($category['seo_description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>

        <label for="status">Statut</label>
        <select id="status" name="status">
            <option value="active" <?= (($category['status'] ?? 'active') === 'active') ? 'selected' : '' ?>>active</option>
            <option value="hidden" <?= (($category['status'] ?? '') === 'hidden') ? 'selected' : '' ?>>hidden</option>
        </select>

        <div class="actions">
            <button type="submit">Enregistrer</button>
        </div>
    </form>
</body>
</html>
