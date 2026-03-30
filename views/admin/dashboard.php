<?php

declare(strict_types=1);

$stats = isset($stats) && is_array($stats) ? $stats : [];
$latestArticles = isset($latestArticles) && is_array($latestArticles) ? $latestArticles : [];

$escape = static fn(string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
?>
<h1>Dashboard BackOffice</h1>

<section>
    <h2>Statistiques</h2>
    <ul>
        <li>Articles: <?= (int) ($stats['articles'] ?? 0) ?></li>
        <li>Categories: <?= (int) ($stats['categories'] ?? 0) ?></li>
        <li>Utilisateurs: <?= (int) ($stats['users'] ?? 0) ?></li>
    </ul>
</section>

<section>
    <h2>Derniers articles</h2>
    <?php if ($latestArticles === []): ?>
        <p>Aucun article pour le moment.</p>
    <?php else: ?>
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr>
                    <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:8px;">Titre</th>
                    <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:8px;">Statut</th>
                    <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:8px;">Auteur</th>
                    <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:8px;">Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($latestArticles as $article): ?>
                    <tr>
                        <td style="border-bottom:1px solid #f3f4f6;padding:8px;"><?= $escape((string) ($article['title'] ?? '')) ?></td>
                        <td style="border-bottom:1px solid #f3f4f6;padding:8px;"><?= $escape((string) ($article['status'] ?? '')) ?></td>
                        <td style="border-bottom:1px solid #f3f4f6;padding:8px;"><?= $escape((string) ($article['author_username'] ?? '')) ?></td>
                        <td style="border-bottom:1px solid #f3f4f6;padding:8px;"><?= $escape((string) ($article['created_at'] ?? '')) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
