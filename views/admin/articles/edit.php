<?php

declare(strict_types=1);

$article = is_array($article ?? null) ? $article : [];
$categories = is_array($categories ?? null) ? $categories : [];
$errors = is_array($errors ?? null) ? $errors : [];
$old = is_array($old ?? null) ? $old : [];
$flash = is_array($flash ?? null) ? $flash : [];
$csrfToken = (string) ($csrfToken ?? '');
$mediaScope = (string) ($mediaScope ?? ($old['media_scope'] ?? ''));

$h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$field = static function (string $key, string $default = '') use ($old): string {
    $value = $old[$key] ?? $default;
    return is_string($value) ? $value : (string) $value;
};

$error = static fn(string $key): string => isset($errors[$key]) ? (string) $errors[$key] : '';

$articleId = (int) ($article['id'] ?? 0);
$currentImage = (string) ($article['image'] ?? '');
?>
<style>
    /* === Canva-Style Header === */
    .article-editor-topbar {
        position: fixed;
        top: 0;
        left: 280px;
        right: 0;
        height: 64px;
        background: #FFFFFF;
        border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 2rem;
        z-index: 1000;
        backdrop-filter: blur(8px);
        box-shadow: 0 1px 0 rgba(0, 0, 0, 0.04);
    }

    .article-editor-topbar h1 {
        margin: 0;
        font-size: 1.125rem;
        font-weight: 600;
        color: #1A1A1A;
    }

    .article-editor-actions {
        display: flex;
        gap: 0.625rem;
        align-items: center;
    }

    .article-editor-actions .btn {
        height: 40px;
        padding: 0 1.25rem;
        border-radius: 8px;
        font-size: 0.9375rem;
        font-weight: 500;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .article-editor-actions .btn-outline {
        background: transparent;
        color: #1A1A1A;
        border: 1px solid rgba(0, 0, 0, 0.15);
    }

    .article-editor-actions .btn-outline:hover {
        background: rgba(0, 0, 0, 0.04);
        border-color: rgba(0, 0, 0, 0.2);
        transform: none;
        box-shadow: none;
    }

    .article-editor-actions .btn-primary {
        background: linear-gradient(135deg, #BC0000 0%, #9A0000 100%);
        color: #FFFFFF;
        box-shadow: 0 2px 8px rgba(188, 0, 0, 0.25);
    }

    .article-editor-actions .btn-primary:hover {
        box-shadow: 0 4px 12px rgba(188, 0, 0, 0.35);
        transform: translateY(-1px);
    }

    /* Content spacing for fixed header */
    .article-editor-content-wrapper {
        padding-top: 80px;
    }

    @media (max-width: 768px) {
        .article-editor-topbar {
            left: 0;
            padding: 0 1rem;
        }
        .article-editor-topbar h1 {
            font-size: 1rem;
        }
        .article-editor-actions {
            gap: 0.5rem;
        }
        .article-editor-actions .btn {
            padding: 0 0.875rem;
            font-size: 0.875rem;
        }
    }
    .article-editor-alert {
        margin-bottom: 1rem;
        border: 1px solid var(--color-border);
        border-left: 4px solid var(--color-accent);
        border-radius: 12px;
        background: var(--color-bg-card);
        padding: 1rem 1.25rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }
    .article-editor-alert.success { border-left-color: #15803d; }
    .article-editor-alert.error { border-left-color: #b91c1c; }

    .article-editor-layout {
        display: grid;
        grid-template-columns: 340px 1fr;
        gap: 1.5rem;
    }
    .article-editor-sidebar,
    .article-editor-main { min-width: 0; }
    .article-editor-sidebar .card,
    .article-editor-main .card {
        margin-bottom: 0;
        border-radius: 16px;
        border: 1px solid rgba(0, 0, 0, 0.08);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04), 0 0 0 1px rgba(0, 0, 0, 0.02);
        overflow: hidden;
    }

    .article-editor-sidebar .card-header,
    .article-editor-main .card-header {
        background: linear-gradient(180deg, #FAFAFA 0%, #F5F5F5 100%);
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        padding: 1.25rem 1.5rem;
        font-weight: 600;
        font-size: 1rem;
    }

    .article-editor-stack { display: grid; gap: 1.5rem; }
    .article-editor-divider { height: 1px; background: rgba(0, 0, 0, 0.06); margin: 1.5rem 0; border: 0; }
    .article-editor-heading { margin: 0 0 0.75rem; font-size: 1rem; font-weight: 600; color: #1A1A1A; }
    .article-editor-subheading { margin: 0 0 0.625rem; font-size: 0.9375rem; font-weight: 600; color: #1A1A1A; }
    .article-editor-helper { color: #666666; font-size: 0.875rem; line-height: 1.5; }
    .article-editor-error { margin-top: 0.5rem; color: #b91c1c; font-size: 0.875rem; font-weight: 500; }
    .article-editor-current-image {
        margin-bottom: 0.875rem;
        border: 1px solid var(--color-border);
        border-radius: var(--radius-sm);
        padding: 0.625rem;
        background: #fafafa;
    }
    .img-preview {
        max-width: 100%;
        border-radius: var(--radius-sm);
        border: 1px solid var(--color-border);
        display: block;
        margin: 0 auto 0.625rem;
    }

    /* === Modern WYSIWYG Toolbar (Canva-style) === */
    .wysiwyg-container {
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 16px;
        background: #FFFFFF;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    }

    .wysiwyg-toolbar {
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        padding: 1rem 1.25rem;
        display: flex;
        gap: 0.25rem;
        flex-wrap: wrap;
        align-items: center;
        background: linear-gradient(180deg, #FAFAFA 0%, #F8F8F8 100%);
    }

    .wysiwyg-btn {
        background: #FFFFFF;
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        padding: 0.5rem 0.875rem;
        cursor: pointer;
        font-size: 0.875rem;
        color: #1A1A1A;
        line-height: 1.2;
        font-weight: 500;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 36px;
        height: 36px;
    }

    .wysiwyg-btn:hover {
        border-color: rgba(0, 0, 0, 0.18);
        background: #F5F5F5;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
    }

    .wysiwyg-btn.is-active {
        background: linear-gradient(135deg, #BC0000 0%, #9A0000 100%);
        border-color: #BC0000;
        color: #FFFFFF;
        box-shadow: 0 2px 6px rgba(188, 0, 0, 0.3);
    }

    .wysiwyg-btn.is-active:hover {
        box-shadow: 0 3px 8px rgba(188, 0, 0, 0.4);
    }

    .article-editor-toolbar-separator {
        width: 1px;
        height: 32px;
        background: rgba(0, 0, 0, 0.1);
        margin: 0 0.5rem;
    }

    .toolbar-group {
        display: flex;
        gap: 0.25rem;
        align-items: center;
        padding: 0.25rem;
        background: rgba(0, 0, 0, 0.02);
        border-radius: 10px;
    }

    .article-editor-title-wrap { padding: 1.5rem 2rem 0; }
    .article-editor-title {
        width: 100%;
        border: 0;
        border-bottom: 2px solid transparent;
        padding: 0 0 0.75rem;
        margin: 0;
        background: transparent;
        font-size: clamp(1.875rem, 3vw, 2.5rem);
        font-weight: 700;
        line-height: 1.2;
        color: #1A1A1A;
        transition: border-color 0.2s;
        white-space: pre-wrap;
        word-wrap: break-word;
        overflow-wrap: break-word;
        word-break: break-word;
        resize: none;
        overflow: hidden;
        font-family: inherit;
    }
    .article-editor-title:focus-visible {
        outline: none;
        border-bottom-color: #BC0000;
        box-shadow: 0 2px 0 0 rgba(188, 0, 0, 0.1);
    }

    .wysiwyg-editor {
        min-height: 500px;
        padding: 2rem;
        outline: none;
        line-height: 1.75;
        font-size: 1.0625rem;
        color: #1A1A1A;
        background: #FFFFFF;
        word-wrap: break-word;
        overflow-wrap: break-word;
        word-break: break-word;
    }
    .wysiwyg-editor h1,
    .wysiwyg-editor h2,
    .wysiwyg-editor h3,
    .wysiwyg-editor p,
    .wysiwyg-editor blockquote { margin: 0; }
    .wysiwyg-editor.is-drop-target {
        background: linear-gradient(135deg, rgba(188, 0, 0, 0.02) 0%, rgba(188, 0, 0, 0.05) 100%);
        box-shadow: inset 0 0 0 2px rgba(188, 0, 0, 0.3);
    }

    /* Form Controls */
    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        background: #FFFFFF;
        color: #1A1A1A;
        font-family: inherit;
        font-size: 0.9375rem;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        font-weight: 400;
    }

    .form-control:hover {
        border-color: rgba(0, 0, 0, 0.18);
    }

    .form-control:focus {
        outline: none;
        border-color: #BC0000;
        box-shadow: 0 0 0 4px rgba(188, 0, 0, 0.1);
        background: #FFFFFF;
    }

    .form-control:disabled {
        background-color: #F5F5F5;
        color: #999999;
        cursor: not-allowed;
    }

    select.form-control {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg width='12' height='8' viewBox='0 0 12 8' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1L6 6L11 1' stroke='%231A1A1A' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        padding-right: 3rem;
    }

    textarea.form-control {
        resize: vertical;
        line-height: 1.6;
    }

    /* === Modern Image Upload (Canva-style) === */
    .form-group {
        margin-bottom: 1.25rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.625rem;
        font-weight: 600;
        font-size: 0.9375rem;
        color: #1A1A1A;
    }

    .editor-figure { margin: 1.25rem 0; display: block; position: relative; }
    .editor-inline-image { max-width: 100%; border-radius: var(--radius-md); display: block; margin: 0 auto; }
    .editor-figure-delete {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        border: 0;
        border-radius: 999px;
        padding: 0.3rem 0.55rem;
        font-size: 0.75rem;
        color: #fff;
        background: rgba(185, 28, 28, 0.95);
        cursor: pointer;
    }
    .editor-figcaption {
        margin-top: 0.5rem;
        text-align: center;
        color: var(--color-text-muted);
        font-size: 0.875rem;
        outline: none;
    }
    .editor-figcaption:focus { color: var(--color-text-main); }
    .wysiwyg-editor blockquote,
    .editor-template-quote {
        margin: 1rem 0;
        padding: 0.75rem 0.875rem;
        border-left: 4px solid #1d4ed8;
        background: #eff6ff;
        color: #1e293b;
        font-style: italic;
        border-radius: 6px;
    }

    .media-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
        gap: 0.625rem;
        max-height: 360px;
        overflow-y: auto;
    }
    .media-item {
        background: var(--color-bg-card);
        border-radius: var(--radius-sm);
        cursor: pointer;
        border: 1px solid var(--color-border);
        width: 100%;
        padding: 0.45rem;
        text-align: left;
        display: grid;
        gap: 0.4rem;
    }
    .media-item:hover { background: #f5f5f5; border-color: #bdbdbd; }
    .media-item img {
        width: 100%;
        height: 88px;
        object-fit: cover;
        border-radius: var(--radius-sm);
        border: 1px solid #eeeeee;
        display: block;
    }
    .media-item-name {
        font-size: 0.75rem;
        color: var(--color-text-main);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .media-empty { margin: 0; font-size: 0.875rem; color: var(--color-text-muted); }
    .media-feedback { margin-top: 0.625rem; font-size: 0.8125rem; color: var(--color-text-muted); }
    .media-feedback[data-level="error"] { color: #b91c1c; }
    .media-feedback[data-level="success"] { color: #166534; }

    .article-editor-meta-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }
    .article-editor-meta-full { grid-column: 1 / -1; }

    .wysiwyg-container.is-fullscreen {
        position: fixed;
        inset: 0;
        z-index: 9999;
        border-radius: 0;
        border: 0;
        box-shadow: none;
        display: flex;
        flex-direction: column;
        background: #ffffff;
    }
    .editor-fullscreen-active { overflow: hidden; }
    .wysiwyg-container.is-fullscreen .wysiwyg-toolbar {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #ffffff;
    }
    .wysiwyg-container.is-fullscreen .wysiwyg-editor {
        flex: 1;
        min-height: 0;
        overflow: auto;
        padding-bottom: 120px;
    }

    .editor-template { margin: 1.2rem 0; border-radius: 10px; }
    .editor-template-hero {
        padding: 20px;
        color: #f8fafc;
        background: linear-gradient(135deg, #0f172a, #1d4ed8);
    }
    .editor-template-hero h2,
    .editor-template-hero p {
        margin: 0;
        color: inherit;
    }
    .editor-template-hero p { margin-top: 0.6rem; }
    .editor-template-columns {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }
    .editor-template-columns > div {
        padding: 14px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: #f8fafc;
    }
    .editor-template-columns h3,
    .editor-template-columns p {
        margin: 0;
    }
    .editor-template-columns p { margin-top: 0.5rem; }
    .editor-template blockquote,
    blockquote.editor-template,
    .editor-template-quote {
        margin: 1rem 0;
        padding: 12px 14px;
        border-left: 4px solid #1d4ed8;
        background: #eff6ff;
        color: #1e293b;
        font-style: italic;
    }
    @media (max-width: 1200px) {
        .article-editor-layout { grid-template-columns: 1fr; }
    }
    @media (max-width: 900px) {
        .editor-template-columns { grid-template-columns: 1fr; }
        .article-editor-meta-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="article-editor-topbar">
    <h1>Modifier l'article #<?= $h($articleId) ?></h1>
    <div class="article-editor-actions">
        <a class="btn btn-outline" href="/admin/articles">Retour a la liste</a>
        <button type="submit" form="article-form" class="btn btn-outline" formaction="/admin/articles/preview" formtarget="_blank">Preview</button>
        <button type="submit" form="article-form" class="btn btn-primary" name="submit_action" value="publish_and_view">Publier et voir</button>
        <button type="submit" form="article-form" class="btn btn-primary" data-hook="save-btn">Enregistrer</button>
    </div>
</div>

<div class="article-editor-content-wrapper">

<?php foreach ($flash as $message): ?>
    <div class="article-editor-alert <?= $h((string) ($message['type'] ?? '')) ?>" role="status" aria-live="polite">
        <?= $h((string) ($message['message'] ?? '')) ?>
    </div>
<?php endforeach; ?>

<form id="article-form" method="post" action="/admin/articles/<?= $h($articleId) ?>/update" enctype="multipart/form-data">
    <input type="hidden" name="_token" value="<?= $h($csrfToken) ?>">
    <input type="hidden" name="media_scope" value="<?= $h($mediaScope) ?>">

    <div class="article-editor-layout">
        <aside class="article-editor-sidebar" data-hook="media-sidebar" aria-label="Panneau media">
            <section class="card">
                <div class="card-header">Medias</div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label" for="image">Image principale</label>
                <?php if ($currentImage !== ''): ?>
                    <div class="article-editor-current-image">
                        <img class="img-preview" src="/<?= $h($currentImage) ?>" alt="">
                        <label class="article-editor-helper" style="display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer; color: #b91c1c;">
                            <input type="checkbox" name="remove_image" value="1"> Supprimer l'image
                        </label>
                    </div>
                <?php endif; ?>
                        <input class="form-control" id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp,.gif" data-hook="main-image-upload">
                        <?php if ($error('image') !== ''): ?><div class="article-editor-error"><?= $h($error('image')) ?></div><?php endif; ?>
                    </div>

                    <hr class="article-editor-divider">

                    <h3 class="article-editor-subheading">Bibliotheque</h3>
                    <p class="article-editor-helper mb-1">Clic ou glisser-deposer dans l'editeur.</p>
                    <div class="form-group">
                        <label class="form-label" for="inline-media-upload">Ajouter une image</label>
                        <input class="form-control" id="inline-media-upload" type="file" data-hook="inline-media-upload" accept=".jpg,.jpeg,.png,.webp,.gif">
                    </div>
                    <div class="media-list" data-hook="media-list">
                        <p class="media-empty">Chargement...</p>
                    </div>
                    <p class="media-feedback" data-hook="media-feedback" data-level="info" aria-live="polite"></p>
                </div>
            </section>
        </aside>

        <main class="article-editor-main">
            <div class="article-editor-stack">
                <section class="card" aria-labelledby="editor-section-title">
                    <div class="card-header" id="editor-section-title">Contenu</div>
                    <div class="card-body">
                        <div class="wysiwyg-container" data-hook="wysiwyg-container">
                            <div class="wysiwyg-toolbar" data-hook="wysiwyg-toolbar">
                                <button type="button" class="wysiwyg-btn" data-action="formatBlock" data-value="P">P</button>
                                <button type="button" class="wysiwyg-btn" data-action="formatBlock" data-value="H1">H1</button>
                                <button type="button" class="wysiwyg-btn" data-action="formatBlock" data-value="H2">H2</button>
                                <button type="button" class="wysiwyg-btn" data-action="formatBlock" data-value="H3">H3</button>
                                <button type="button" class="wysiwyg-btn" data-action="formatBlock" data-value="BLOCKQUOTE">Citation</button>
                                <span class="article-editor-toolbar-separator" aria-hidden="true"></span>
                                <button type="button" class="wysiwyg-btn" data-action="bold"><b>B</b></button>
                                <button type="button" class="wysiwyg-btn" data-action="italic"><i>I</i></button>
                                <button type="button" class="wysiwyg-btn" data-action="underline"><u>U</u></button>
                                <span class="article-editor-toolbar-separator" aria-hidden="true"></span>
                                <button type="button" class="wysiwyg-btn" data-action="insertUnorderedList">Liste</button>
                                <button type="button" class="wysiwyg-btn" data-action="insertOrderedList">1. Liste</button>
                                <button type="button" class="wysiwyg-btn" data-action="createLink">Lien</button>
                                <span class="article-editor-toolbar-separator" aria-hidden="true"></span>
                                <button type="button" class="wysiwyg-btn" data-action="justifyLeft">Gauche</button>
                                <button type="button" class="wysiwyg-btn" data-action="justifyCenter">Centre</button>
                                <button type="button" class="wysiwyg-btn" data-action="justifyRight">Droite</button>
                                <span class="article-editor-toolbar-separator" aria-hidden="true"></span>
                                <span class="article-editor-helper" style="margin-right: 0.25rem;">Templates:</span>
                                <button type="button" class="wysiwyg-btn" data-action="insert-template" data-template="hero">Hero</button>
                                <button type="button" class="wysiwyg-btn" data-action="insert-template" data-template="columns">2 Colonnes</button>
                                <button type="button" class="wysiwyg-btn" data-action="insert-template" data-template="quote">Citation bloc</button>
                                <span style="flex-grow: 1;"></span>
                                <button type="button" class="wysiwyg-btn" data-action="toggle-fullscreen" title="Plein ecran">Plein ecran</button>
                            </div>

                            <div class="article-editor-title-wrap">
                                <label class="form-label" for="title">Titre</label>
                                <input
                                    id="title"
                                    class="article-editor-title"
                                    name="title"
                                    type="text"
                                    value="<?= $h($field('title')) ?>"
                                    required
                                    maxlength="255"
                                    placeholder="Titre de l'article..."
                                    <?= $error('title') !== '' ? 'aria-invalid="true"' : '' ?>
                                >
                                <?php if ($error('title') !== ''): ?><div class="article-editor-error"><?= $h($error('title')) ?></div><?php endif; ?>
                            </div>

                            <div class="wysiwyg-editor" contenteditable="true" data-hook="visual-editor" placeholder="Commencez a ecrire votre article ici..."></div>

                            <textarea id="content" name="content" style="display: none;" required data-hook="content-hidden"><?= $h($field('content')) ?></textarea>
                            <?php if ($error('content') !== ''): ?><div class="article-editor-error" style="padding: 0 1.5rem 1rem;"><?= $h($error('content')) ?></div><?php endif; ?>
                        </div>
                    </div>
                </section>

                <section class="card" aria-labelledby="meta-section-title">
                    <div class="card-header" id="meta-section-title">Informations et SEO</div>
                    <div class="card-body">
                        <div class="article-editor-meta-grid">
                            <div class="form-group">
                                <label class="form-label" for="slug">Slug (auto si vide)</label>
                                <input class="form-control" id="slug" name="slug" type="text" value="<?= $h($field('slug')) ?>" maxlength="255" placeholder="mon-article" <?= $error('slug') !== '' ? 'aria-invalid="true"' : '' ?>>
                                <?php if ($error('slug') !== ''): ?><div class="article-editor-error"><?= $h($error('slug')) ?></div><?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="category_id">Categorie</label>
                                <select class="form-control" id="category_id" name="category_id" <?= $error('category_id') !== '' ? 'aria-invalid="true"' : '' ?>>
                                    <option value="">Aucune</option>
                                    <?php $categoryId = $field('category_id'); ?>
                                    <?php foreach ($categories as $category): ?>
                                        <?php $id = (string) ($category['id'] ?? ''); ?>
                                        <option value="<?= $h($id) ?>" <?= $categoryId === $id ? 'selected' : '' ?>>
                                            <?= $h((string) ($category['name'] ?? '')) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ($error('category_id') !== ''): ?><div class="article-editor-error"><?= $h($error('category_id')) ?></div><?php endif; ?>
                            </div>

                            <div class="form-group article-editor-meta-full">
                                <label class="form-label" for="excerpt">Extrait (visible sur les listes)</label>
                                <textarea class="form-control" id="excerpt" name="excerpt" rows="3"><?= $h($field('excerpt')) ?></textarea>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="status">Statut</label>
                                <select class="form-control" id="status" name="status" <?= $error('status') !== '' ? 'aria-invalid="true"' : '' ?>>
                                    <?php $status = $field('status', 'draft'); ?>
                                    <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
                                    <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Published</option>
                                </select>
                                <?php if ($error('status') !== ''): ?><div class="article-editor-error"><?= $h($error('status')) ?></div><?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="published_at">Date de publication</label>
                                <input class="form-control" id="published_at" name="published_at" type="datetime-local" value="<?= $h($field('published_at')) ?>" <?= $error('published_at') !== '' ? 'aria-invalid="true"' : '' ?>>
                                <?php if ($error('published_at') !== ''): ?><div class="article-editor-error"><?= $h($error('published_at')) ?></div><?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="image_alt">Texte alternatif de l'image (SEO)</label>
                                <input class="form-control" id="image_alt" name="image_alt" type="text" value="<?= $h($field('image_alt')) ?>" maxlength="255" <?= $error('image_alt') !== '' ? 'aria-invalid="true"' : '' ?>>
                                <?php if ($error('image_alt') !== ''): ?><div class="article-editor-error"><?= $h($error('image_alt')) ?></div><?php endif; ?>
                            </div>

                            <div class="article-editor-meta-full"><hr class="article-editor-divider"></div>

                            <div class="form-group article-editor-meta-full">
                                <label class="form-label" for="meta_title">Meta Title</label>
                                <input class="form-control" id="meta_title" name="meta_title" type="text" value="<?= $h($field('meta_title')) ?>" maxlength="70" <?= $error('meta_title') !== '' ? 'aria-invalid="true"' : '' ?>>
                                <?php if ($error('meta_title') !== ''): ?><div class="article-editor-error"><?= $h($error('meta_title')) ?></div><?php endif; ?>
                            </div>

                            <div class="form-group article-editor-meta-full">
                                <label class="form-label" for="meta_description">Meta Description</label>
                                <textarea class="form-control" id="meta_description" name="meta_description" rows="2" maxlength="160" <?= $error('meta_description') !== '' ? 'aria-invalid="true"' : '' ?>><?= $h($field('meta_description')) ?></textarea>
                                <?php if ($error('meta_description') !== ''): ?><div class="article-editor-error"><?= $h($error('meta_description')) ?></div><?php endif; ?>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>
</form>

<script src="/assets/js/admin-article-editor.js" defer></script>
