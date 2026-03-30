<?php

declare(strict_types=1);

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
?>
<style>
    .editor-layout { display: flex; flex-direction: column; gap: 24px; font-family: system-ui, -apple-system, sans-serif; margin-top: 20px; }
    @media (min-width: 900px) { .editor-layout { flex-direction: row; align-items: flex-start; } }
    .editor-sidebar { width: 100%; background: #fdfdfd; border: 1px solid #ddd; border-radius: 8px; padding: 16px; box-sizing: border-box; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
    @media (min-width: 900px) { .editor-sidebar { width: 260px; flex-shrink: 0; position: sticky; top: 20px; } }
    .editor-main { flex-grow: 1; display: flex; flex-direction: column; gap: 24px; width: 100%; min-width: 0; }
    
    .wysiwyg-container { border: 1px solid #ddd; border-radius: 8px; background: #fff; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    .wysiwyg-toolbar { background: #f8f9fa; border-bottom: 1px solid #ddd; padding: 10px; display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
    .wysiwyg-btn { background: white; border: 1px solid #ddd; border-radius: 4px; padding: 6px 10px; cursor: pointer; font-size: 14px; color: #444; font-weight: 500; transition: all 0.2s; }
    .wysiwyg-btn:hover { background: #eee; border-color: #ccc; color: #000; }
    .wysiwyg-btn.is-active { background: #111; color: #fff; border-color: #111; }
    .wysiwyg-editor { min-height: 500px; padding: 32px max(40px, 5%); outline: none; line-height: 1.7; font-size: 17px; color: #333; }
    .wysiwyg-editor h1, .wysiwyg-editor h2, .wysiwyg-editor h3, .wysiwyg-editor p, .wysiwyg-editor blockquote { margin: 0; }
    .wysiwyg-editor.is-drop-target { background: #f7fbff; box-shadow: inset 0 0 0 2px #1d4ed8; }
    .editor-figure { margin: 1.4em 0; display: block; }
    .editor-inline-image { max-width: 100%; border-radius: 8px; display: block; margin: 0 auto; }
    .editor-figcaption { margin-top: 8px; text-align: center; color: #555; font-size: 14px; outline: none; }
    .editor-figcaption:focus { color: #222; }
    
    .media-list { display: grid; grid-template-columns: 1fr; gap: 10px; margin-top: 12px; max-height: 340px; overflow-y: auto; }
    .media-item { background: #fff; border-radius: 8px; cursor: pointer; border: 1px solid #ddd; transition: all 0.2s; width: 100%; padding: 8px; text-align: left; display: grid; gap: 8px; }
    .media-item:hover { background: #f8f8f8; border-color: #aaa; }
    .media-item img { width: 100%; height: 110px; object-fit: cover; border-radius: 6px; border: 1px solid #eee; display: block; }
    .media-item-name { font-size: 12px; color: #333; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .media-empty { margin: 0; font-size: 13px; color: #666; }
    .media-feedback { margin-top: 10px; font-size: 12px; color: #555; }
    .media-feedback[data-level="error"] { color: #b91c1c; }
    .media-feedback[data-level="success"] { color: #166534; }
    
    .meta-grid { display: grid; gap: 16px; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); background: #fdfdfd; padding: 24px; border-radius: 8px; border: 1px solid #ddd; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
    .full { grid-column: 1 / -1; }
    label { display: block; margin-bottom: 6px; font-weight: 600; font-size: 14px; color: #222; }
    input[type="text"], input[type="datetime-local"], select, textarea { width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; font-family: inherit; font-size: 14px; }
    input[type="text"]:focus, select:focus, textarea:focus { border-color: #555; outline: none; box-shadow: 0 0 0 1px #555; }
    textarea { min-height: 80px; resize: vertical; }
    
    .error { color: #d32f2f; font-size: 13px; margin-top: 6px; font-weight: 500; }
    .flash { margin: 8px 0; padding: 12px 16px; border-radius: 6px; font-weight: 500; }
    .flash.success { background: #e7f9ec; color: #115926; border: 1px solid #c3e6cb; }
    .flash.error { background: #ffeaea; color: #8f1515; border: 1px solid #f5c6cb; }
    
    .page-header { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; }
    .actions { display: flex; gap: 12px; flex-wrap: wrap; }
    .btn { display: inline-flex; align-items: center; justify-content: center; border: 1px solid #333; padding: 10px 18px; border-radius: 6px; background: #fff; color: #111; text-decoration: none; font-weight: 500; cursor: pointer; font-size: 15px; transition: all 0.2s; }
    .btn:hover { background: #f5f5f5; }
    .btn.primary { background: #111; color: #fff; border-color: #111; }
    .btn.primary:hover { background: #333; border-color: #333; }
    .btn.publish { border-color: #1f6f43; color: #1f6f43; }
    .btn.publish:hover { background: #eef8f1; }
</style>

<div class="page-header">
    <h1 style="margin:0;">Creer un article</h1>
    <div class="actions">
        <a class="btn" href="/admin/articles">Retour a la liste</a>
        <button type="submit" form="article-form" class="btn publish" name="submit_action" value="publish_and_view">Publier et voir</button>
        <button type="submit" form="article-form" class="btn primary" data-hook="save-btn">Enregistrer</button>
    </div>
</div>

<?php foreach ($flash as $message): ?>
    <div class="flash <?= $h((string) ($message['type'] ?? '')) ?>">
        <?= $h((string) ($message['message'] ?? '')) ?>
    </div>
<?php endforeach; ?>

<form id="article-form" method="post" action="/admin/articles" enctype="multipart/form-data">
    <input type="hidden" name="_token" value="<?= $h($csrfToken) ?>">

    <div class="editor-layout">
        <!-- Sidebar Media -->
        <aside class="editor-sidebar" data-hook="media-sidebar">
            <h3 style="margin-top:0; margin-bottom: 12px; font-size:16px;">Medias</h3>
            <div>
                <label for="image" style="font-size:13px; margin-bottom:4px;">Image Principale</label>
                <input id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp,.gif" data-hook="main-image-upload" style="font-size:13px; width:100%;">
                <?php if ($error('image') !== ''): ?><div class="error"><?= $h($error('image')) ?></div><?php endif; ?>
            </div>
            
            <hr style="border:none; border-top:1px solid #ddd; margin: 20px 0;">
            
            <h4 style="margin:0 0 10px 0; font-size:14px;">Bibliotheque <small style="font-weight:normal; color:#666;">(clic ou glisser-deposer)</small></h4>
            <input type="file" data-hook="inline-media-upload" accept=".jpg,.jpeg,.png,.webp,.gif" style="font-size:13px; width:100%; margin-bottom:10px;">
            <div class="media-list" data-hook="media-list">
                <p class="media-empty">Chargement...</p>
            </div>
            <p class="media-feedback" data-hook="media-feedback" data-level="info"></p>
        </aside>

        <!-- Main Content -->
        <main class="editor-main">
            <!-- WYSIWYG Editor Area -->
            <div class="wysiwyg-container" data-hook="wysiwyg-container">
                <div class="wysiwyg-toolbar" data-hook="wysiwyg-toolbar">
                    <button type="button" class="wysiwyg-btn" data-action="formatBlock" data-value="P">P</button>
                    <button type="button" class="wysiwyg-btn" data-action="formatBlock" data-value="H1">H1</button>
                    <button type="button" class="wysiwyg-btn" data-action="formatBlock" data-value="H2">H2</button>
                    <button type="button" class="wysiwyg-btn" data-action="formatBlock" data-value="H3">H3</button>
                    <button type="button" class="wysiwyg-btn" data-action="formatBlock" data-value="BLOCKQUOTE">Citation</button>
                    <div style="width:1px; height:24px; background:#ddd; margin:0 4px;"></div>
                    <button type="button" class="wysiwyg-btn" data-action="bold"><b>B</b></button>
                    <button type="button" class="wysiwyg-btn" data-action="italic"><i>I</i></button>
                    <button type="button" class="wysiwyg-btn" data-action="underline"><u>U</u></button>
                    <div style="width:1px; height:24px; background:#ddd; margin:0 4px;"></div>
                    <button type="button" class="wysiwyg-btn" data-action="insertUnorderedList">&bull; Liste</button>
                    <button type="button" class="wysiwyg-btn" data-action="insertOrderedList">1. Liste</button>
                    <button type="button" class="wysiwyg-btn" data-action="createLink">Lien</button>
                    <div style="width:1px; height:24px; background:#ddd; margin:0 4px;"></div>
                    <button type="button" class="wysiwyg-btn" data-action="justifyLeft">Gauche</button>
                    <button type="button" class="wysiwyg-btn" data-action="justifyCenter">Centre</button>
                    <button type="button" class="wysiwyg-btn" data-action="justifyRight">Droite</button>
                </div>
                
                <div style="padding: 24px max(40px, 5%) 0 max(40px, 5%);">
                    <input id="title" name="title" type="text" value="<?= $h($field('title')) ?>" required maxlength="255" placeholder="Titre de l'article..." style="font-size: 32px; font-weight: bold; border: none; padding: 0; box-shadow: none; width: 100%; border-bottom: 2px solid transparent; background: transparent; outline: none; margin-bottom: 8px;">
                    <?php if ($error('title') !== ''): ?><div class="error" style="margin-top:0; margin-bottom:16px;"><?= $h($error('title')) ?></div><?php endif; ?>
                </div>

                <div class="wysiwyg-editor" contenteditable="true" data-hook="visual-editor" placeholder="Commencez a ecrire votre article ici..."></div>
                
                <!-- Hidden textarea for backend submission -->
                <textarea id="content" name="content" style="display:none;" required data-hook="content-hidden"><?= $h($field('content')) ?></textarea>
                <?php if ($error('content') !== ''): ?><div class="error" style="padding: 0 40px 20px;"><?= $h($error('content')) ?></div><?php endif; ?>
            </div>

            <!-- Metadata Area -->
            <div class="meta-grid">
                <div class="full"><h3 style="margin:0;">Informations & SEO</h3></div>
                
                <div>
                    <label for="slug">Slug (auto si vide)</label>
                    <input id="slug" name="slug" type="text" value="<?= $h($field('slug')) ?>" maxlength="255" placeholder="mon-article">
                    <?php if ($error('slug') !== ''): ?><div class="error"><?= $h($error('slug')) ?></div><?php endif; ?>
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

                <div class="full">
                    <label for="excerpt">Extrait (visible sur les listes)</label>
                    <textarea id="excerpt" name="excerpt" rows="3"><?= $h($field('excerpt')) ?></textarea>
                </div>

                <div>
                    <label for="status">Statut</label>
                    <select id="status" name="status">
                        <?php $status = $field('status', 'draft'); ?>
                        <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Published</option>
                    </select>
                    <?php if ($error('status') !== ''): ?><div class="error"><?= $h($error('status')) ?></div><?php endif; ?>
                </div>

                <div>
                    <label for="published_at">Date de publication</label>
                    <input id="published_at" name="published_at" type="datetime-local" value="<?= $h($field('published_at')) ?>">
                    <?php if ($error('published_at') !== ''): ?><div class="error"><?= $h($error('published_at')) ?></div><?php endif; ?>
                </div>

                <div>
                    <label for="image_alt">Texte alternatif de l'image (SEO)</label>
                    <input id="image_alt" name="image_alt" type="text" value="<?= $h($field('image_alt')) ?>" maxlength="255">
                    <?php if ($error('image_alt') !== ''): ?><div class="error"><?= $h($error('image_alt')) ?></div><?php endif; ?>
                </div>
                
                <div class="full"><hr style="border:none; border-top:1px solid #eee; margin: 8px 0;"></div>

                <div class="full">
                    <label for="meta_title">Meta Title</label>
                    <input id="meta_title" name="meta_title" type="text" value="<?= $h($field('meta_title')) ?>" maxlength="70">
                    <?php if ($error('meta_title') !== ''): ?><div class="error"><?= $h($error('meta_title')) ?></div><?php endif; ?>
                </div>

                <div class="full">
                    <label for="meta_description">Meta Description</label>
                    <textarea id="meta_description" name="meta_description" rows="2" maxlength="160"><?= $h($field('meta_description')) ?></textarea>
                    <?php if ($error('meta_description') !== ''): ?><div class="error"><?= $h($error('meta_description')) ?></div><?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</form>

<script src="/assets/js/admin-article-editor.js" defer></script>
