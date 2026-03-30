<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Models\Article;

final class HomeController
{
    private Article $articleModel;

    public function __construct(?Article $articleModel = null)
    {
        $this->articleModel = $articleModel ?? new Article();
    }

    public function index(): void
    {
        $result = $this->articleModel->listPaginated(1, 8, 'published');
        $publishedArticles = array_values(array_filter(
            $result['items'] ?? [],
            static function (array $article): bool {
                $publishedAt = (string) ($article['published_at'] ?? '');

                return $publishedAt !== '' && strtotime($publishedAt) !== false && strtotime($publishedAt) <= time();
            }
        ));

        $mainArticle = $publishedArticles[0] ?? null;
        $latestArticles = array_slice($publishedArticles, 1, 6);

        $pageTitle = 'Accueil - Guerre Iran Irak';
        $metaDescription = 'Actualites et analyses sur la guerre Iran-Irak: contexte, enjeux et evolutions recentes.';
        $ogImage = '/assets/images/default-og.jpg';

        if (is_array($mainArticle)) {
            $pageTitle = (string) ($mainArticle['meta_title'] ?? '') !== ''
                ? (string) $mainArticle['meta_title']
                : $pageTitle;

            $metaDescription = (string) ($mainArticle['meta_description'] ?? '') !== ''
                ? (string) $mainArticle['meta_description']
                : $metaDescription;

            if ((string) ($mainArticle['image'] ?? '') !== '') {
                $ogImage = '/' . ltrim((string) $mainArticle['image'], '/');
            }
        }

        $view = APP_ROOT . '/views/front/home.php';
        require APP_ROOT . '/views/front/layout.php';
    }
}
