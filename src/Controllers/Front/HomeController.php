<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Models\Article;

final class HomeController
{
    private Article $articleModel;
    private const ARTICLES_PER_PAGE = 10;

    public function __construct(?Article $articleModel = null)
    {
        $this->articleModel = $articleModel ?? new Article();
    }

    public function index(): void
    {
        $result = $this->articleModel->listPaginated(1, self::ARTICLES_PER_PAGE, 'published');
        $publishedArticles = $this->filterPublished($result['items'] ?? []);
        $totalArticles = $result['total'] ?? 0;

        $mainArticle = $publishedArticles[0] ?? null;
        $secondaryArticles = array_slice($publishedArticles, 1, 4);
        $latestArticles = array_slice($publishedArticles, 5);

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

        $this->render('front/home', [
            'mainArticle' => $mainArticle,
            'secondaryArticles' => $secondaryArticles,
            'latestArticles' => $latestArticles,
            'totalArticles' => $totalArticles,
            'currentPage' => 1,
            'articlesPerPage' => self::ARTICLES_PER_PAGE,
        ], $pageTitle, $metaDescription, $ogImage);
    }

    /**
     * API endpoint for infinite scroll - returns JSON
     */
    public function loadMore(): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $result = $this->articleModel->listPaginated($page, self::ARTICLES_PER_PAGE, 'published');
        $publishedArticles = $this->filterPublished($result['items'] ?? []);

        $articles = [];
        foreach ($publishedArticles as $article) {
            $articles[] = [
                'id' => $article['id'],
                'title' => $article['title'],
                'slug' => $article['slug'],
                'excerpt' => $article['excerpt'] ?? '',
                'image' => $article['image'] ?? '',
                'image_alt' => $article['image_alt'] ?? $article['title'],
                'category_name' => $article['category_name'] ?? '',
                'category_slug' => $article['category_slug'] ?? '',
                'published_at' => $article['published_at'] ?? '',
            ];
        }

        echo json_encode([
            'success' => true,
            'articles' => $articles,
            'hasMore' => count($publishedArticles) === self::ARTICLES_PER_PAGE,
            'page' => $page,
            'total' => $result['total'] ?? 0,
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param array<int, array<string, mixed>> $articles
     * @return array<int, array<string, mixed>>
     */
    private function filterPublished(array $articles): array
    {
        return array_values(array_filter(
            $articles,
            static function (array $article): bool {
                $publishedAt = (string) ($article['published_at'] ?? '');
                // Si pas de date de publication, considérer comme publié immédiatement
                if ($publishedAt === '') {
                    return true;
                }
                // Sinon, vérifier que la date est passée ou égale à maintenant
                return strtotime($publishedAt) !== false && strtotime($publishedAt) <= time();
            }
        ));
    }

    private function render(string $view, array $data, string $pageTitle, string $metaDescription, string $ogImage): void
    {
        $viewPath = APP_ROOT . '/views/' . $view . '.php';
        $layoutPath = APP_ROOT . '/views/front/layout.php';

        if (!is_readable($viewPath) || !is_readable($layoutPath)) {
            http_response_code(500);
            header('Content-Type: text/plain; charset=UTF-8');
            echo '500 View not found';
            return;
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewPath;
        $content = (string) ob_get_clean();

        require $layoutPath;
    }
}
