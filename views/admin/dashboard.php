<?php

declare(strict_types=1);

$stats = isset($stats) && is_array($stats) ? $stats : [];
$latestArticles = isset($latestArticles) && is_array($latestArticles) ? $latestArticles : [];

$escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
?>
<section class="mb-3">
    <div class="d-flex justify-content-between align-items-center mb-2" style="flex-wrap: wrap; gap: 0.75rem;">
        <h1 style="margin: 0;">Dashboard BackOffice</h1>
        <span class="badge badge-primary">Vue d'ensemble</span>
    </div>
    <p class="mb-1" style="margin-top: 0; color: var(--color-text-muted);">Suivi rapide des indicateurs et des derniers contenus publies.</p>
</section>

<section class="mb-3">
    <div class="d-flex gap-2" style="flex-wrap: wrap;">
        <article class="card" style="flex: 1 1 220px; margin-bottom: 0;">
            <div class="card-header">Articles</div>
            <div class="card-body">
                <p style="margin: 0; font-size: 2rem; font-weight: 700;"><?= (int) ($stats['articles'] ?? 0) ?></p>
                <p class="mb-1" style="margin-top: 0.25rem; color: var(--color-text-muted);">Total des articles</p>
            </div>
        </article>

        <article class="card" style="flex: 1 1 220px; margin-bottom: 0;">
            <div class="card-header">Categories</div>
            <div class="card-body">
                <p style="margin: 0; font-size: 2rem; font-weight: 700;"><?= (int) ($stats['categories'] ?? 0) ?></p>
                <p class="mb-1" style="margin-top: 0.25rem; color: var(--color-text-muted);">Classes de contenu</p>
            </div>
        </article>

        <article class="card" style="flex: 1 1 220px; margin-bottom: 0;">
            <div class="card-header">Utilisateurs</div>
            <div class="card-body">
                <p style="margin: 0; font-size: 2rem; font-weight: 700;"><?= (int) ($stats['users'] ?? 0) ?></p>
                <p class="mb-1" style="margin-top: 0.25rem; color: var(--color-text-muted);">Comptes admin actifs</p>
            </div>
        </article>
    </div>
</section>

<section class="card">
    <div class="card-header d-flex justify-content-between align-items-center" style="gap: 0.75rem; flex-wrap: wrap;">
        <span>Derniers articles</span>
        <span class="badge badge-neutral"><?= count($latestArticles) ?> affiches</span>
    </div>

    <div class="card-body">
        <?php if ($latestArticles === []): ?>
            <div class="alert alert-info mb-1">Aucun article pour le moment.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Statut</th>
                            <th>Auteur</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($latestArticles as $article): ?>
                            <tr>
                                <td><?= $escape((string) ($article['title'] ?? '')) ?></td>
                                <td><span class="badge badge-neutral"><?= $escape((string) ($article['status'] ?? '')) ?></span></td>
                                <td><?= $escape((string) ($article['author_username'] ?? '')) ?></td>
                                <td><?= $escape((string) ($article['created_at'] ?? '')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>
