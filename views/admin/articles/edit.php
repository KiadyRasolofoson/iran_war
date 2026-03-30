<?php

declare(strict_types=1);

$article = is_array($article ?? null) ? $article : [];
$categories = is_array($categories ?? null) ? $categories : [];
$errors = is_array($errors ?? null) ? $errors : [];
$old = is_array($old ?? null) ? $old : [];
$flash = is_array($flash ?? null) ? $flash : [];
$csrfToken = (string) ($csrfToken ?? '');

$h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$field = static function (string $key, string $default = '') use ($old): string {
    $value = $old[$key] ?? $default;
    return is_string($value) ? $value : (string) $value;
};

$error = static fn(string $key): string => isset($errors[$key]) ? (string) $errors[$key] : '';

$articleId = (int) ($article['id'] ?? 0);
$currentImage = (string) ($article['image'] ?? '');
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editer un article</title>
    <style>
        body { font-family: "Segoe UI", Tahoma, sans-serif; margin: 24px; color: #1b1b1b; }
        h1 { margin: 0 0 16px; }
        .grid { display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); }
        .full { grid-column: 1 / -1; }
        label { display: block; margin-bottom: 6px; font-weight: 600; }
        input, select, textarea { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
        textarea { min-height: 120px; }
        .error { color: #a61212; font-size: 13px; margin-top: 4px; }
        .flash { margin: 8px 0; padding: 10px; border-radius: 6px; }
        .flash.success { background: #e7f9ec; color: #115926; }
        .flash.error { background: #ffeaea; color: #8f1515; }
        .actions { display: flex; gap: 10px; margin-top: 16px; flex-wrap: wrap; }
        .btn { display: inline-block; border: 1px solid #333; padding: 8px 12px; border-radius: 6px; background: #fff; color: #111; text-decoration: none; }
        .btn.primary { background: #111; color: #fff; }
        .img-preview { margin-top: 8px; max-width: 260px; border-radius: 8px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <h1>Editer l'article #<?= $h($articleId) ?></h1>
    <p><a class="btn" href="/admin/articles">Retour a la liste</a></p>

    <?php foreach ($flash as $message): ?>
        <div class="flash <?= $h((string) ($message['type'] ?? '')) ?>">
            <?= $h((string) ($message['message'] ?? '')) ?>
        </div>
    <?php endforeach; ?>

    <form method="post" action="/admin/articles/<?= $h($articleId) ?>/update" enctype="multipart/form-data">
        <input type="hidden" name="_token" value="<?= $h($csrfToken) ?>">

        <div class="grid">
            <div class="full">
                <label for="title">Titre *</label>
                <input id="title" name="title" type="text" value="<?= $h($field('title')) ?>" required maxlength="255">
                <?php if ($error('title') !== ''): ?><div class="error"><?= $h($error('title')) ?></div><?php endif; ?>
            </div>

            <div class="full">
                <label for="slug">Slug (auto si vide)</label>
                <input id="slug" name="slug" type="text" value="<?= $h($field('slug')) ?>" maxlength="255" placeholder="mon-article">
                <?php if ($error('slug') !== ''): ?><div class="error"><?= $h($error('slug')) ?></div><?php endif; ?>
            </div>

            <div class="full">
                <label for="excerpt">Excerpt</label>
                <textarea id="excerpt" name="excerpt" rows="4"><?= $h($field('excerpt')) ?></textarea>
            </div>

            <div class="full">
                <label for="content">Contenu *</label>
                <textarea id="content" name="content" rows="10" required><?= $h($field('content')) ?></textarea>
                <?php if ($error('content') !== ''): ?><div class="error"><?= $h($error('content')) ?></div><?php endif; ?>
            </div>

            <div>
                <label for="image">Image</label>
                <input id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp,.gif">
                <?php if ($currentImage !== ''): ?>
                    <div>
                        <small>Image actuelle: <code><?= $h($currentImage) ?></code></small><br>
                        <img class="img-preview" src="/<?= $h($currentImage) ?>" alt="">
                    </div>
                    <label style="margin-top: 8px; font-weight: 400;">
                        <input type="checkbox" name="remove_image" value="1" style="width: auto;"> Supprimer l'image actuelle
                    </label>
                <?php endif; ?>
                <?php if ($error('image') !== ''): ?><div class="error"><?= $h($error('image')) ?></div><?php endif; ?>
            </div>

            <div>
                <label for="image_alt">Image Alt</label>
                <input id="image_alt" name="image_alt" type="text" value="<?= $h($field('image_alt')) ?>" maxlength="255">
                <?php if ($error('image_alt') !== ''): ?><div class="error"><?= $h($error('image_alt')) ?></div><?php endif; ?>
            </div>

            <div>
                <label for="meta_title">Meta Title</label>
                <input id="meta_title" name="meta_title" type="text" value="<?= $h($field('meta_title')) ?>" maxlength="70">
                <?php if ($error('meta_title') !== ''): ?><div class="error"><?= $h($error('meta_title')) ?></div><?php endif; ?>
            </div>

            <div>
                <label for="meta_description">Meta Description</label>
                <textarea id="meta_description" name="meta_description" rows="3" maxlength="160"><?= $h($field('meta_description')) ?></textarea>
                <?php if ($error('meta_description') !== ''): ?><div class="error"><?= $h($error('meta_description')) ?></div><?php endif; ?>
            </div>

            <div>
                <label for="status">Status</label>
                <select id="status" name="status">
                    <?php $status = $field('status', 'draft'); ?>
                    <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Published</option>
                </select>
                <?php if ($error('status') !== ''): ?><div class="error"><?= $h($error('status')) ?></div><?php endif; ?>
            </div>

            <div>
                <label for="category_id">Categorie</label>
                <select id="category_id" name="category_id">
                    <option value="">Aucune</option>
                    <?php $categoryId = $field('category_id'); ?>
                    <?php foreach ($categories as $category): ?>
                        <?php $id = (string) ($category['id'] ?? ''); ?>
                        <option value="<?= $h($id) ?>" <?= $categoryId === $id ? 'selected' : '' ?>>
                            <?= $h((string) ($category['name'] ?? '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($error('category_id') !== ''): ?><div class="error"><?= $h($error('category_id')) ?></div><?php endif; ?>
            </div>

            <div>
                <label for="published_at">Published At</label>
                <input id="published_at" name="published_at" type="datetime-local" value="<?= $h($field('published_at')) ?>">
                <?php if ($error('published_at') !== ''): ?><div class="error"><?= $h($error('published_at')) ?></div><?php endif; ?>
            </div>
        </div>

        <div class="actions">
            <button type="submit" class="btn primary">Enregistrer les modifications</button>
            <a class="btn" href="/admin/articles">Retour</a>
        </div>
    </form>
</body>
</html>
