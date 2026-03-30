<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Uploader;
use App\Models\Article;
use App\Models\Category;

final class ArticleController
{
    private const MEDIA_SCOPE_PATTERN = '/\A(?:draft-[a-z0-9]{24}|article-[1-9][0-9]*)\z/';

    private Article $articleModel;
    private Category $categoryModel;
    private Auth $auth;
    private Uploader $uploader;

    public function __construct(
        ?Article $articleModel = null,
        ?Category $categoryModel = null,
        ?Auth $auth = null,
        ?Uploader $uploader = null
    ) {
        $this->articleModel = $articleModel ?? new Article();
        $this->categoryModel = $categoryModel ?? new Category();
        $this->auth = $auth ?? new Auth();
        $this->uploader = $uploader ?? new Uploader();

        $this->auth->requireLogin();
    }

    public function index(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 10;
        $search = trim((string) ($_GET['q'] ?? ''));
        $status = trim((string) ($_GET['status'] ?? ''));
        $status = in_array($status, ['draft', 'published'], true) ? $status : null;

        $categoryId = null;
        if (isset($_GET['category_id']) && $_GET['category_id'] !== '') {
            $categoryId = (int) $_GET['category_id'];
            if ($categoryId <= 0) {
                $categoryId = null;
            }
        }

        if ($search !== '') {
            $result = $this->articleModel->searchPaginated($search, $page, $perPage, $status, $categoryId);
        } else {
            $result = $this->articleModel->listPaginated($page, $perPage, $status);
        }

        $this->render('admin/articles/index', [
            'articles' => $result['items'] ?? [],
            'pagination' => $result['pagination'] ?? [],
            'filters' => [
                'q' => $search,
                'status' => $status ?? '',
                'category_id' => $categoryId,
            ],
            'categories' => $this->categoryModel->list(200, 0),
            'csrfToken' => $this->auth->token(),
            'flash' => $this->pullFlash(),
        ], 'Administration des articles');
    }

    public function create(): void
    {
        $old = $this->consumeOldForm();
        $mediaScope = $this->resolveCreateMediaScope(is_array($old['data'] ?? null) ? $old['data'] : []);
        $formData = $old['data'] ?: $this->defaultFormData();
        $formData['media_scope'] = $mediaScope;

        $this->render('admin/articles/create', [
            'categories' => $this->categoryModel->list(200, 0),
            'csrfToken' => $this->auth->token(),
            'errors' => $old['errors'],
            'old' => $formData,
            'mediaScope' => $mediaScope,
            'flash' => $this->pullFlash(),
        ], 'Creer un article');
    }

    public function preview(): void
    {
        $this->assertPostWithCsrf();

        $input = $this->collectInput($_POST);
        $content = $this->sanitizeContentHtml($input['content']);

        $category = null;
        $categoryId = (int) $input['category_id'];
        if ($categoryId > 0) {
            $category = $this->categoryModel->findById($categoryId);
        }

        $title = trim($input['title']);
        if ($title === '') {
            $title = 'Apercu article';
        }

        $slug = trim($input['slug']) !== '' ? $this->slugify($input['slug']) : $this->slugify($title);
        if ($slug === '') {
            $slug = 'apercu-article';
        }

        $excerpt = trim($input['excerpt']) !== ''
            ? trim($input['excerpt'])
            : mb_substr(strip_tags($content), 0, 180);

        $article = [
            'id' => 0,
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $excerpt,
            'content' => $content !== '' ? $content : '<p>Aucun contenu pour cet apercu.</p>',
            'image' => null,
            'image_alt' => trim($input['image_alt']) !== '' ? trim($input['image_alt']) : 'Image de couverture',
            'meta_title' => trim($input['meta_title']) !== '' ? trim($input['meta_title']) : $title,
            'meta_description' => trim($input['meta_description']) !== '' ? trim($input['meta_description']) : $excerpt,
            'status' => 'published',
            'published_at' => date('Y-m-d H:i:s'),
            'category_id' => $categoryId > 0 ? $categoryId : null,
            'category_name' => is_array($category) ? (string) ($category['name'] ?? '') : '',
            'category_slug' => is_array($category) ? (string) ($category['slug'] ?? '') : '',
        ];

        $this->renderFrontPreview($article);
    }

    public function store(): void
    {
        $this->assertPostWithCsrf();
        $publishAndView = (string) ($_POST['submit_action'] ?? '') === 'publish_and_view';

        $input = $this->collectInput($_POST);
        $mediaScope = $this->resolveCreateMediaScope($input);
        $input['media_scope'] = $mediaScope;
        $validation = $this->validateInput($input, null);

        if (!empty($validation['errors'])) {
            $this->storeOldForm($validation['errors'], $input);
            $this->flash('error', 'Please fix the form errors.');
            $this->redirect('/admin/articles/create');
        }

        $payload = $validation['payload'];

        if (isset($_FILES['image']) && is_array($_FILES['image']) && (int) ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            try {
                $payload['image'] = $this->uploader->upload($_FILES['image'], 'articles/' . $mediaScope);
            } catch (\Throwable $exception) {
                $this->storeOldForm(['image' => $exception->getMessage()], $input);
                $this->flash('error', 'Image upload failed.');
                $this->redirect('/admin/articles/create');
            }
        }

        $user = $this->auth->user();
        $payload['author_id'] = (int) ($user['id'] ?? 0);

        if ($payload['author_id'] <= 0) {
            $this->storeOldForm(['author' => 'Unable to resolve the current user.'], $input);
            $this->flash('error', 'Authentication state is invalid.');
            $this->redirect('/admin/articles/create');
        }

        if ($publishAndView) {
            $payload['status'] = 'published';
            $payload['published_at'] = date('Y-m-d H:i:s');
        }

        $createdId = $this->articleModel->create($payload);
        $stableScope = $this->buildArticleMediaScope($createdId);

        if ($this->isDraftMediaScope($mediaScope)) {
            $migrated = $this->migrateMediaScopeDirectory($mediaScope, $stableScope);

            if ($migrated) {
                $content = (string) ($payload['content'] ?? '');
                $image = isset($payload['image']) && is_string($payload['image']) ? $payload['image'] : null;

                $rewrittenContent = $this->rewriteMediaScopePaths($content, $mediaScope, $stableScope);
                $rewrittenImage = $image !== null
                    ? $this->rewriteMediaScopePaths($image, $mediaScope, $stableScope)
                    : null;

                $afterCreatePayload = ['content' => $rewrittenContent];
                if ($rewrittenImage !== null) {
                    $afterCreatePayload['image'] = $rewrittenImage;
                }

                $this->articleModel->update($createdId, $afterCreatePayload);
            }
        }

        if ($publishAndView) {
            $slug = $this->resolveArticleSlug($createdId, (string) ($payload['slug'] ?? ''));
            if ($slug !== null) {
                $this->redirect('/article/' . rawurlencode($slug));
            }

            $this->flash('error', 'Article was published but the front URL could not be resolved.');
            $this->redirect('/admin/articles/' . $createdId . '/edit');
        }

        $this->flash('success', 'Article created (ID: ' . $createdId . ').');
        $this->redirect('/admin/articles');
    }

    public function edit($id): void
    {
        $articleId = (int) $id;
        $article = $this->articleModel->findById($articleId);

        if ($article === null) {
            $this->abortNotFound('Article not found.');
        }

        $old = $this->consumeOldForm();
        $formData = $old['data'] ?: $this->mapArticleToFormData($article);
        $mediaScope = $this->buildArticleMediaScope($articleId);
        $formData['media_scope'] = $mediaScope;

        $this->render('admin/articles/edit', [
            'article' => $article,
            'categories' => $this->categoryModel->list(200, 0),
            'csrfToken' => $this->auth->token(),
            'errors' => $old['errors'],
            'old' => $formData,
            'mediaScope' => $mediaScope,
            'flash' => $this->pullFlash(),
        ], 'Modifier un article');
    }

    public function update($id): void
    {
        $this->assertPostWithCsrf();
        $publishAndView = (string) ($_POST['submit_action'] ?? '') === 'publish_and_view';

        $articleId = (int) $id;
        $article = $this->articleModel->findById($articleId);

        if ($article === null) {
            $this->abortNotFound('Article not found.');
        }

        $input = $this->collectInput($_POST);
        $mediaScope = $this->sanitizeMediaScope((string) ($input['media_scope'] ?? ''), $articleId);
        $input['media_scope'] = $mediaScope;
        $validation = $this->validateInput($input, $articleId);

        if (!empty($validation['errors'])) {
            $this->storeOldForm($validation['errors'], $input);
            $this->flash('error', 'Please fix the form errors.');
            $this->redirect('/admin/articles/' . $articleId . '/edit');
        }

        $payload = $validation['payload'];

        if (isset($_POST['remove_image']) && $_POST['remove_image'] === '1') {
            $payload['image'] = null;
        }

        if (isset($_FILES['image']) && is_array($_FILES['image']) && (int) ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            try {
                $payload['image'] = $this->uploader->upload($_FILES['image'], 'articles/' . $mediaScope);
            } catch (\Throwable $exception) {
                $this->storeOldForm(['image' => $exception->getMessage()], $input);
                $this->flash('error', 'Image upload failed.');
                $this->redirect('/admin/articles/' . $articleId . '/edit');
            }
        }

        if ($publishAndView) {
            $payload['status'] = 'published';
            $payload['published_at'] = date('Y-m-d H:i:s');
        }

        $updated = $this->articleModel->update($articleId, $payload);

        if ($publishAndView) {
            $slug = $this->resolveArticleSlug(
                $articleId,
                (string) ($payload['slug'] ?? ($article['slug'] ?? ''))
            );

            if ($slug !== null) {
                $this->redirect('/article/' . rawurlencode($slug));
            }

            $this->flash('error', 'Article was published but the front URL could not be resolved.');
            $this->redirect('/admin/articles/' . $articleId . '/edit');
        }

        if ($updated) {
            $this->flash('success', 'Article updated successfully.');
        } else {
            $this->flash('error', 'No changes were applied.');
        }

        $this->redirect('/admin/articles/' . $articleId . '/edit');
    }

    public function delete($id): void
    {
        $this->assertPostWithCsrf();

        $articleId = (int) $id;
        $deleted = $this->articleModel->delete($articleId);

        if ($deleted) {
            $this->flash('success', 'Article deleted successfully.');
        } else {
            $this->flash('error', 'Unable to delete article or article not found.');
        }

        $this->redirect('/admin/articles');
    }

    public function toggleStatus($id): void
    {
        $this->assertPostWithCsrf();

        $articleId = (int) $id;
        $article = $this->articleModel->findById($articleId);

        if ($article === null) {
            $this->flash('error', 'Article not found.');
            $this->redirect('/admin/articles');
        }

        $currentStatus = (string) ($article['status'] ?? 'draft');
        $nextStatus = $currentStatus === 'published' ? 'draft' : 'published';

        $payload = ['status' => $nextStatus];
        if ($nextStatus === 'published') {
            $payload['published_at'] = date('Y-m-d H:i:s');
        } else {
            $payload['published_at'] = null;
        }

        $updated = $this->articleModel->update($articleId, $payload);

        if ($updated) {
            $this->flash('success', 'Status updated to: ' . $nextStatus . '.');
        } else {
            $this->flash('error', 'Unable to toggle status.');
        }

        $this->redirect('/admin/articles');
    }

    private function collectInput(array $input): array
    {
        return [
            'title' => trim((string) ($input['title'] ?? '')),
            'slug' => trim((string) ($input['slug'] ?? '')),
            'excerpt' => trim((string) ($input['excerpt'] ?? '')),
            'content' => trim((string) ($input['content'] ?? '')),
            'image_alt' => trim((string) ($input['image_alt'] ?? '')),
            'meta_title' => trim((string) ($input['meta_title'] ?? '')),
            'meta_description' => trim((string) ($input['meta_description'] ?? '')),
            'status' => trim((string) ($input['status'] ?? 'draft')),
            'category_id' => trim((string) ($input['category_id'] ?? '')),
            'published_at' => trim((string) ($input['published_at'] ?? '')),
            'media_scope' => trim((string) ($input['media_scope'] ?? '')),
        ];
    }

    private function resolveCreateMediaScope(array $input): string
    {
        return $this->sanitizeMediaScope((string) ($input['media_scope'] ?? ''), null);
    }

    private function sanitizeMediaScope(string $scope, ?int $articleId): string
    {
        $stableScope = $articleId !== null && $articleId > 0 ? $this->buildArticleMediaScope($articleId) : null;

        $scope = strtolower(trim($scope));
        if ($scope !== '' && preg_match(self::MEDIA_SCOPE_PATTERN, $scope) === 1) {
            if ($stableScope !== null) {
                return $stableScope;
            }

            return $scope;
        }

        if ($stableScope !== null) {
            return $stableScope;
        }

        return $this->generateDraftMediaScope();
    }

    private function generateDraftMediaScope(): string
    {
        return 'draft-' . bin2hex(random_bytes(12));
    }

    private function buildArticleMediaScope(int $articleId): string
    {
        return 'article-' . max(1, $articleId);
    }

    private function isDraftMediaScope(string $scope): bool
    {
        return str_starts_with($scope, 'draft-');
    }

    private function migrateMediaScopeDirectory(string $fromScope, string $toScope): bool
    {
        $fromScope = strtolower(trim($fromScope));
        $toScope = strtolower(trim($toScope));

        if ($fromScope === '' || $toScope === '' || $fromScope === $toScope) {
            return false;
        }

        if (preg_match(self::MEDIA_SCOPE_PATTERN, $fromScope) !== 1 || preg_match(self::MEDIA_SCOPE_PATTERN, $toScope) !== 1) {
            return false;
        }

        $sourceDir = APP_ROOT . '/public/uploads/articles/' . $fromScope;
        $targetDir = APP_ROOT . '/public/uploads/articles/' . $toScope;

        if (!is_dir($sourceDir) || is_dir($targetDir)) {
            return false;
        }

        $parentDir = dirname($targetDir);
        if (!is_dir($parentDir)) {
            return false;
        }

        return rename($sourceDir, $targetDir);
    }

    private function rewriteMediaScopePaths(string $value, string $fromScope, string $toScope): string
    {
        $fromScope = strtolower(trim($fromScope));
        $toScope = strtolower(trim($toScope));

        if ($value === '' || $fromScope === '' || $toScope === '' || $fromScope === $toScope) {
            return $value;
        }

        $fromPath = 'uploads/articles/' . $fromScope . '/';
        $toPath = 'uploads/articles/' . $toScope . '/';

        return str_replace(
            ['/' . $fromPath, $fromPath],
            ['/' . $toPath, $toPath],
            $value
        );
    }

    private function validateInput(array $input, ?int $articleId): array
    {
        $errors = [];

        $title = $input['title'];
        if ($title === '') {
            $errors['title'] = 'Title is required.';
        } elseif (mb_strlen($title) > 255) {
            $errors['title'] = 'Title must be at most 255 characters.';
        }

        $slug = $input['slug'] !== '' ? $this->slugify($input['slug']) : $this->slugify($title);
        if ($slug === '') {
            $errors['slug'] = 'Unable to generate a valid slug.';
        } elseif (mb_strlen($slug) > 255) {
            $errors['slug'] = 'Slug must be at most 255 characters.';
        } else {
            $slug = $this->buildUniqueSlug($slug, $articleId);
        }

        $content = $this->sanitizeContentHtml($input['content']);
        if ($content === '') {
            $errors['content'] = 'Content is required.';
        }

        $status = $input['status'];
        if (!in_array($status, ['draft', 'published'], true)) {
            $errors['status'] = 'Status is invalid.';
        }

        $categoryId = null;
        if ($input['category_id'] !== '') {
            $categoryId = (int) $input['category_id'];
            if ($categoryId <= 0 || $this->categoryModel->findById($categoryId) === null) {
                $errors['category_id'] = 'Category is invalid.';
            }
        }

        $publishedAt = null;
        if ($input['published_at'] !== '') {
            $publishedAt = $this->normalizeDateTimeInput($input['published_at']);
            if ($publishedAt === null) {
                $errors['published_at'] = 'Published date must use a valid date-time format.';
            }
        }

        if ($status === 'published' && $publishedAt === null) {
            $publishedAt = date('Y-m-d H:i:s');
        }

        if (mb_strlen($input['meta_title']) > 70) {
            $errors['meta_title'] = 'Meta title must be at most 70 characters.';
        }

        if (mb_strlen($input['meta_description']) > 160) {
            $errors['meta_description'] = 'Meta description must be at most 160 characters.';
        }

        if (mb_strlen($input['image_alt']) > 255) {
            $errors['image_alt'] = 'Image alt must be at most 255 characters.';
        }

        $payload = [
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $input['excerpt'] !== '' ? $input['excerpt'] : null,
            'content' => $content,
            'image_alt' => $input['image_alt'] !== '' ? $input['image_alt'] : null,
            'meta_title' => $input['meta_title'] !== '' ? $input['meta_title'] : null,
            'meta_description' => $input['meta_description'] !== '' ? $input['meta_description'] : null,
            'status' => $status,
            'category_id' => $categoryId,
            'published_at' => $publishedAt,
        ];

        return [
            'errors' => $errors,
            'payload' => $payload,
        ];
    }

    private function sanitizeContentHtml(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        if (!class_exists(\DOMDocument::class)) {
            return $this->sanitizeContentHtmlFallback($html);
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');

        $previousLibxmlState = libxml_use_internal_errors(true);
        $loaded = $dom->loadHTML(
            '<?xml encoding="utf-8" ?><div id="content-root">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previousLibxmlState);

        if (!$loaded) {
            return $this->sanitizeContentHtmlFallback($html);
        }

        $root = $dom->getElementById('content-root');
        if (!$root instanceof \DOMElement) {
            return $this->sanitizeContentHtmlFallback($html);
        }

        $this->sanitizeContentNodeTree($root);

        $sanitized = '';
        $child = $root->firstChild;
        while ($child !== null) {
            $sanitized .= (string) $dom->saveHTML($child);
            $child = $child->nextSibling;
        }

        return trim($sanitized);
    }

    private function sanitizeContentNodeTree(\DOMNode $parent): void
    {
        $child = $parent->firstChild;

        while ($child !== null) {
            $next = $child->nextSibling;

            if ($child instanceof \DOMComment) {
                $parent->removeChild($child);
                $child = $next;
                continue;
            }

            if ($child instanceof \DOMElement) {
                $this->sanitizeContentElement($child);
            }

            $child = $next;
        }
    }

    private function sanitizeContentElement(\DOMElement $element): void
    {
        $tagName = strtolower($element->tagName);
        $dangerousTags = [
            'script', 'style', 'iframe', 'object', 'embed', 'applet', 'link', 'meta', 'base', 'form',
            'input', 'button', 'textarea', 'select', 'option', 'noscript', 'svg', 'math',
        ];

        if (in_array($tagName, $dangerousTags, true)) {
            if ($element->parentNode !== null) {
                $element->parentNode->removeChild($element);
            }
            return;
        }

        $allowedAttributesByTag = [
            'h1' => ['class', 'style', 'align'],
            'h2' => ['class', 'style', 'align'],
            'h3' => ['class', 'style', 'align'],
            'h4' => ['class', 'style', 'align'],
            'h5' => ['class', 'style', 'align'],
            'h6' => ['class', 'style', 'align'],
            'p' => ['class', 'style', 'align'],
            'ul' => ['class', 'style', 'align'],
            'ol' => ['class', 'style', 'align'],
            'li' => ['class', 'style', 'align'],
            'strong' => ['class'],
            'em' => ['class'],
            'b' => ['class'],
            'i' => ['class'],
            'u' => ['class'],
            'br' => [],
            'blockquote' => ['class', 'style', 'align'],
            'a' => ['href', 'title', 'target', 'rel', 'class'],
            'img' => ['src', 'alt', 'title', 'width', 'height', 'loading', 'class', 'align'],
            'figure' => ['class', 'style', 'align'],
            'figcaption' => ['class', 'style', 'align'],
            'hr' => ['class'],
            'div' => ['class', 'style', 'align'],
            'span' => ['class', 'style', 'align'],
        ];

        if (!array_key_exists($tagName, $allowedAttributesByTag)) {
            $this->unwrapNode($element);
            return;
        }

        $allowedAttributes = $allowedAttributesByTag[$tagName];
        $attributesToRemove = [];

        foreach ($element->attributes as $attribute) {
            $attrName = strtolower($attribute->name);
            $attrValue = $attribute->value;

            if (str_starts_with($attrName, 'on')) {
                $attributesToRemove[] = $attribute->name;
                continue;
            }

            if (!in_array($attrName, $allowedAttributes, true)) {
                $attributesToRemove[] = $attribute->name;
                continue;
            }

            if ($attrName === 'href' || $attrName === 'src') {
                $sanitizedUrl = $this->sanitizeContentUrl($attrValue, $attrName === 'src');
                if ($sanitizedUrl === null) {
                    $attributesToRemove[] = $attribute->name;
                } else {
                    $element->setAttribute($attribute->name, $sanitizedUrl);
                }
                continue;
            }

            if ($attrName === 'style') {
                $sanitizedStyle = $this->sanitizeInlineStyle($attrValue);
                if ($sanitizedStyle === '') {
                    $attributesToRemove[] = $attribute->name;
                } else {
                    $element->setAttribute($attribute->name, $sanitizedStyle);
                }
                continue;
            }

            if ($attrName === 'class') {
                $sanitizedClass = $this->sanitizeClassAttribute($attrValue);
                if ($sanitizedClass === '') {
                    $attributesToRemove[] = $attribute->name;
                } else {
                    $element->setAttribute($attribute->name, $sanitizedClass);
                }
                continue;
            }

            if ($attrName === 'align') {
                $sanitizedAlign = $this->sanitizeAlignAttribute($attrValue);
                if ($sanitizedAlign === null) {
                    $attributesToRemove[] = $attribute->name;
                } else {
                    $element->setAttribute($attribute->name, $sanitizedAlign);
                }
                continue;
            }

            if ($attrName === 'target') {
                $target = strtolower(trim($attrValue));
                if (!in_array($target, ['_blank', '_self'], true)) {
                    $attributesToRemove[] = $attribute->name;
                } else {
                    $element->setAttribute($attribute->name, $target);
                }
                continue;
            }

            if ($attrName === 'rel') {
                $sanitizedRel = $this->sanitizeRelAttribute($attrValue);
                if ($sanitizedRel === '') {
                    $attributesToRemove[] = $attribute->name;
                } else {
                    $element->setAttribute($attribute->name, $sanitizedRel);
                }
                continue;
            }

            if (($attrName === 'width' || $attrName === 'height') && !preg_match('/^[1-9][0-9]{0,3}$/', trim($attrValue))) {
                $attributesToRemove[] = $attribute->name;
                continue;
            }

            if ($attrName === 'loading') {
                $loading = strtolower(trim($attrValue));
                if (!in_array($loading, ['lazy', 'eager'], true)) {
                    $attributesToRemove[] = $attribute->name;
                } else {
                    $element->setAttribute($attribute->name, $loading);
                }
            }
        }

        foreach (array_unique($attributesToRemove) as $attributeName) {
            $element->removeAttribute($attributeName);
        }

        if ($tagName === 'a' && strtolower($element->getAttribute('target')) === '_blank') {
            $rel = $this->sanitizeRelAttribute($element->getAttribute('rel'));
            $tokens = $rel === '' ? [] : (preg_split('/\s+/', $rel) ?: []);
            if (!in_array('noopener', $tokens, true)) {
                $tokens[] = 'noopener';
            }
            if (!in_array('noreferrer', $tokens, true)) {
                $tokens[] = 'noreferrer';
            }
            $element->setAttribute('rel', implode(' ', array_filter($tokens)));
        }

        $this->sanitizeContentNodeTree($element);
    }

    private function unwrapNode(\DOMElement $node): void
    {
        $parent = $node->parentNode;
        if ($parent === null) {
            return;
        }

        while ($node->firstChild !== null) {
            $parent->insertBefore($node->firstChild, $node);
        }

        $parent->removeChild($node);
    }

    private function sanitizeContentUrl(string $url, bool $isImageSource): ?string
    {
        $url = trim($url);
        if ($url === '') {
            return null;
        }

        if (preg_match('/[\x00-\x1F\x7F]/', $url) === 1) {
            return null;
        }

        $decoded = html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $scheme = parse_url($decoded, PHP_URL_SCHEME);
        if (is_string($scheme)) {
            $scheme = strtolower($scheme);
            $allowedSchemes = $isImageSource ? ['http', 'https'] : ['http', 'https', 'mailto', 'tel'];
            if (!in_array($scheme, $allowedSchemes, true)) {
                return null;
            }

            if ($isImageSource) {
                $normalizedLocalPath = $this->normalizeLocalImageUrlToPath($decoded);
                if ($normalizedLocalPath !== null) {
                    return $normalizedLocalPath;
                }
            }
        }

        return $url;
    }

    private function normalizeLocalImageUrlToPath(string $url): ?string
    {
        $host = parse_url($url, PHP_URL_HOST);
        $path = parse_url($url, PHP_URL_PATH);
        if (!is_string($host) || !is_string($path) || $path === '') {
            return null;
        }

        $normalizedHost = strtolower(trim($host));
        $localHosts = $this->resolveLocalContentHosts();
        if (!in_array($normalizedHost, $localHosts, true)) {
            return null;
        }

        $query = parse_url($url, PHP_URL_QUERY);
        $fragment = parse_url($url, PHP_URL_FRAGMENT);

        $result = $path;
        if (is_string($query) && $query !== '') {
            $result .= '?' . $query;
        }
        if (is_string($fragment) && $fragment !== '') {
            $result .= '#' . $fragment;
        }

        return $result;
    }

    /**
     * @return array<int, string>
     */
    private function resolveLocalContentHosts(): array
    {
        $hosts = ['localhost', '127.0.0.1'];

        $appUrlHost = parse_url((string) APP_URL, PHP_URL_HOST);
        if (is_string($appUrlHost) && $appUrlHost !== '') {
            $hosts[] = strtolower($appUrlHost);
        }

        $httpHost = (string) ($_SERVER['HTTP_HOST'] ?? '');
        if ($httpHost !== '') {
            $httpHost = strtolower(trim(explode(':', $httpHost)[0] ?? ''));
            if ($httpHost !== '') {
                $hosts[] = $httpHost;
            }
        }

        return array_values(array_unique($hosts));
    }

    private function sanitizeInlineStyle(string $style): string
    {
        $style = trim($style);
        if ($style === '') {
            return '';
        }

        $cleaned = [];
        $declarations = explode(';', $style);
        foreach ($declarations as $declaration) {
            $declaration = trim($declaration);
            if ($declaration === '') {
                continue;
            }

            [$property, $value] = array_pad(explode(':', $declaration, 2), 2, '');
            $property = strtolower(trim($property));
            $value = strtolower(trim($value));

            if ($property !== 'text-align') {
                continue;
            }

            if (!in_array($value, ['left', 'right', 'center', 'justify'], true)) {
                continue;
            }

            $cleaned[] = $property . ':' . $value;
        }

        return implode('; ', $cleaned);
    }

    private function sanitizeClassAttribute(string $classValue): string
    {
        $classValue = trim($classValue);
        if ($classValue === '') {
            return '';
        }

        $classes = preg_split('/\s+/', $classValue) ?: [];
        $sanitized = [];

        foreach ($classes as $className) {
            if (preg_match('/^[a-zA-Z0-9_-]{1,64}$/', $className) === 1) {
                $sanitized[] = $className;
            }
        }

        return implode(' ', array_values(array_unique($sanitized)));
    }

    private function sanitizeAlignAttribute(string $alignValue): ?string
    {
        $alignValue = strtolower(trim($alignValue));
        if ($alignValue === '') {
            return null;
        }

        if (!in_array($alignValue, ['left', 'right', 'center', 'justify'], true)) {
            return null;
        }

        return $alignValue;
    }

    private function sanitizeRelAttribute(string $relValue): string
    {
        $relValue = strtolower(trim($relValue));
        if ($relValue === '') {
            return '';
        }

        $allowed = ['noopener', 'noreferrer', 'nofollow', 'ugc', 'sponsored'];
        $tokens = preg_split('/\s+/', $relValue) ?: [];
        $sanitized = [];

        foreach ($tokens as $token) {
            if (in_array($token, $allowed, true)) {
                $sanitized[] = $token;
            }
        }

        return implode(' ', array_values(array_unique($sanitized)));
    }

    private function sanitizeContentHtmlFallback(string $html): string
    {
        $allowedTags = '<h1><h2><h3><h4><h5><h6><p><ul><ol><li><strong><em><b><i><u><br><blockquote><a><img><figure><figcaption><hr><div><span>';
        $html = strip_tags($html, $allowedTags);
        $html = preg_replace("/\\s+on[a-z]+\\s*=\\s*(?:\"[^\"]*\"|'[^']*'|[^\\s>]+)/i", '', $html) ?? '';
        $html = preg_replace("/\\s(?:href|src)\\s*=\\s*(?:\"\\s*(?:javascript|data):[^\"]*\"|'\\s*(?:javascript|data):[^']*'|\\s*(?:javascript|data):[^\\s>]+)/i", '', $html) ?? '';

        return trim($html);
    }

    private function normalizeDateTimeInput(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $formats = ['Y-m-d\\TH:i', 'Y-m-d H:i:s', 'Y-m-d H:i'];

        foreach ($formats as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $value);
            if ($date instanceof \DateTimeImmutable) {
                return $date->format('Y-m-d H:i:s');
            }
        }

        return null;
    }

    private function buildUniqueSlug(string $baseSlug, ?int $articleId): string
    {
        $candidate = $baseSlug;
        $suffix = 2;

        while (true) {
            $existing = $this->articleModel->findBySlug($candidate);
            if ($existing === null) {
                return $candidate;
            }

            if ($articleId !== null && (int) ($existing['id'] ?? 0) === $articleId) {
                return $candidate;
            }

            $candidate = $baseSlug . '-' . $suffix;
            $suffix++;

            if ($suffix > 1000) {
                return $baseSlug . '-' . time();
            }
        }
    }

    private function resolveArticleSlug(int $articleId, string $fallbackSlug = ''): ?string
    {
        $article = $this->articleModel->findById($articleId);

        if ($article !== null) {
            $slug = trim((string) ($article['slug'] ?? ''));
            if ($slug !== '') {
                return $slug;
            }
        }

        $fallbackSlug = trim($fallbackSlug);

        return $fallbackSlug !== '' ? $fallbackSlug : null;
    }

    private function slugify(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($transliterated !== false) {
            $value = $transliterated;
        }

        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';

        return trim($value, '-');
    }

    private function mapArticleToFormData(array $article): array
    {
        $publishedAt = '';
        if (!empty($article['published_at']) && is_string($article['published_at'])) {
            $dt = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $article['published_at']);
            if ($dt instanceof \DateTimeImmutable) {
                $publishedAt = $dt->format('Y-m-d\\TH:i');
            }
        }

        return [
            'title' => (string) ($article['title'] ?? ''),
            'slug' => (string) ($article['slug'] ?? ''),
            'excerpt' => (string) ($article['excerpt'] ?? ''),
            'content' => (string) ($article['content'] ?? ''),
            'image_alt' => (string) ($article['image_alt'] ?? ''),
            'meta_title' => (string) ($article['meta_title'] ?? ''),
            'meta_description' => (string) ($article['meta_description'] ?? ''),
            'status' => (string) ($article['status'] ?? 'draft'),
            'category_id' => (string) ($article['category_id'] ?? ''),
            'published_at' => $publishedAt,
            'media_scope' => '',
        ];
    }

    private function defaultFormData(): array
    {
        return [
            'title' => '',
            'slug' => '',
            'excerpt' => '',
            'content' => '',
            'image_alt' => '',
            'meta_title' => '',
            'meta_description' => '',
            'status' => 'draft',
            'category_id' => '',
            'published_at' => '',
            'media_scope' => '',
        ];
    }

    private function storeOldForm(array $errors, array $data): void
    {
        $_SESSION['admin_articles_form'] = [
            'errors' => $errors,
            'data' => $data,
        ];
    }

    private function consumeOldForm(): array
    {
        $default = ['errors' => [], 'data' => []];

        $stored = $_SESSION['admin_articles_form'] ?? null;
        unset($_SESSION['admin_articles_form']);

        if (!is_array($stored)) {
            return $default;
        }

        return [
            'errors' => is_array($stored['errors'] ?? null) ? $stored['errors'] : [],
            'data' => is_array($stored['data'] ?? null) ? $stored['data'] : [],
        ];
    }

    private function assertPostWithCsrf(): void
    {
        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));

        if ($method !== 'POST') {
            http_response_code(405);
            header('Content-Type: text/plain; charset=UTF-8');
            echo '405 Method Not Allowed';
            exit;
        }

        if (!$this->auth->verifyToken()) {
            http_response_code(419);
            header('Content-Type: text/plain; charset=UTF-8');
            echo '419 CSRF Token Mismatch';
            exit;
        }
    }

    private function flash(string $type, string $message): void
    {
        $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
    }

    private function pullFlash(): array
    {
        $messages = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);

        return is_array($messages) ? $messages : [];
    }

    private function render(string $view, array $data = [], string $title = 'Administration'): void
    {
        $viewPath = APP_ROOT . '/views/' . ltrim($view, '/');
        if (!str_ends_with($viewPath, '.php')) {
            $viewPath .= '.php';
        }
        $layoutPath = APP_ROOT . '/views/admin/layout.php';

        if (!is_readable($viewPath) || !is_readable($layoutPath)) {
            throw new \RuntimeException('View not found: ' . $viewPath);
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $viewPath;
        $content = (string) ob_get_clean();

        $authUser = $this->auth->user();
        $showAdminNav = true;
        $csrfToken = $this->auth->token();

        require $layoutPath;
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }

    private function abortNotFound(string $message): void
    {
        http_response_code(404);
        header('Content-Type: text/plain; charset=UTF-8');
        echo $message;
        exit;
    }

    private function renderFrontPreview(array $article): void
    {
        $viewPath = APP_ROOT . '/views/front/articles/show.php';
        $layoutPath = APP_ROOT . '/views/front/layout.php';

        if (!is_readable($viewPath) || !is_readable($layoutPath)) {
            http_response_code(500);
            header('Content-Type: text/plain; charset=UTF-8');
            echo '500 Preview view not found';
            exit;
        }

        $relatedArticles = [];
        $pageTitle = (string) ($article['meta_title'] ?? $article['title'] ?? 'Apercu');
        $metaDescription = (string) ($article['meta_description'] ?? $article['excerpt'] ?? 'Apercu article');
        $ogImage = '/assets/images/default-og.jpg';

        ob_start();
        require $viewPath;
        $content = (string) ob_get_clean();

        require $layoutPath;
        exit;
    }
}
