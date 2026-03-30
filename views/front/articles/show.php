<?php

declare(strict_types=1);

$article = is_array($article ?? null) ? $article : [];
$relatedArticles = is_array($relatedArticles ?? null) ? $relatedArticles : [];

$h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$title = (string) ($article['title'] ?? 'Article');
$categoryName = trim((string) ($article['category_name'] ?? ''));

$resolveImageUrl = static function (?string $rawPath): ?string {
    $path = trim((string) $rawPath);
    if ($path === '') {
        return null;
    }

    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }

    if (str_starts_with($path, '/')) {
        return $path;
    }

    return '/' . ltrim($path, '/');
};

$featuredImage = $resolveImageUrl($article['image'] ?? null);
$imageAlt = trim((string) ($article['image_alt'] ?? ''));
if ($imageAlt === '') {
    $imageAlt = 'Image de couverture de l\'article ' . $title;
}
?>
<div style="max-width:930px;margin:0 auto;padding:22px 16px 44px;">
    <nav aria-label="Fil d'Ariane article" style="margin-bottom:14px;display:flex;gap:10px;flex-wrap:wrap;">
        <a href="/articles" style="color:#7c2d12;text-decoration:none;">Tous les articles</a>
        <?php if ($categoryName !== '' && (string) ($article['category_slug'] ?? '') !== ''): ?>
            <a href="/categorie/<?= $h((string) ($article['category_slug'] ?? '')) ?>" style="color:#7c2d12;text-decoration:none;">Categorie: <?= $h($categoryName) ?></a>
        <?php endif; ?>
    </nav>

    <article style="background:#fff;border:1px solid #d6d3d1;border-radius:12px;overflow:hidden;">
        <?php if ($featuredImage !== null): ?>
            <img src="<?= $h($featuredImage) ?>" alt="<?= $h($imageAlt) ?>" style="width:100%;max-height:420px;object-fit:cover;background:#e7e5e4;display:block;">
        <?php else: ?>
            <div role="img" aria-label="<?= $h($imageAlt) ?>" style="height:220px;background:#e7e5e4;"></div>
        <?php endif; ?>

        <div style="padding:18px;">
            <h1 style="margin:0 0 8px;font-size:2.3rem;line-height:1.15;"><?= $h($title) ?></h1>
            <p style="margin:0 0 16px;color:#6b7280;">
                Publie le <?= $h((string) ($article['published_at'] ?? 'Date inconnue')) ?>
                <?php if ($categoryName !== ''): ?>
                    | Categorie: <a href="/categorie/<?= $h((string) ($article['category_slug'] ?? '')) ?>"><?= $h($categoryName) ?></a>
                <?php endif; ?>
            </p>

            <div class="editor-rich-content" style="line-height:1.65;">
                <?= (string) ($article['content'] ?? '') ?>
            </div>

            <?php if ($relatedArticles !== []): ?>
                <section aria-label="Articles lies" style="margin-top:20px;border-top:1px dashed #d6d3d1;padding-top:14px;">
                    <h2 style="margin:0 0 8px;font-size:1.2rem;">Articles lies</h2>
                    <ul style="margin:0;padding-left:18px;">
                        <?php foreach ($relatedArticles as $related): ?>
                            <li>
                                <a href="/article/<?= $h((string) ($related['slug'] ?? '')) ?>" style="color:#7c2d12;"><?= $h((string) ($related['title'] ?? 'Article')) ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endif; ?>
        </div>
    </article>
</div>
