<?php

declare(strict_types=1);

namespace App\Controllers\Front;

use App\Models\Article;
use App\Models\Category;

final class ArticleController
{
    private Article $articleModel;
    private Category $categoryModel;

    public function __construct(?Article $articleModel = null, ?Category $categoryModel = null)
    {
        $this->articleModel = $articleModel ?? new Article();
        $this->categoryModel = $categoryModel ?? new Category();
    }

    public function index(): void
    {
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 9;

        $search = trim((string) ($_GET['q'] ?? ''));
        $categorySlug = trim((string) ($_GET['category'] ?? ''));
        $dateFilter = $this->normalizeDateFilter($_GET['date'] ?? null);

        $selectedCategory = null;
        $categoryId = null;

        if ($categorySlug !== '') {
            $selectedCategory = $this->categoryModel->findBySlug($categorySlug);
            if ($selectedCategory === null) {
                $this->notFound('Category not found.');
            }

            $categoryId = (int) ($selectedCategory['id'] ?? 0);
            if ($categoryId <= 0) {
                $this->notFound('Category not found.');
            }
        }

        $result = $this->queryPublishedArticles($search, $page, $perPage, $categoryId);

        if ($dateFilter !== null) {
            $result = $this->applyDateFilterToPaginatedQuery(
                $dateFilter,
                $page,
                $perPage,
                function (int $sourcePage, int $sourcePerPage) use ($search, $categoryId): array {
                    return $this->queryPublishedArticles($search, $sourcePage, $sourcePerPage, $categoryId);
                }
            );
        }

        $categories = array_values(array_filter(
            $this->categoryModel->list(300, 0),
            static fn(array $category): bool => (string) ($category['status'] ?? 'hidden') === 'active'
        ));

        $pageTitle = 'Articles - Guerre Iran Irak';
        if ($selectedCategory !== null && (string) ($selectedCategory['name'] ?? '') !== '') {
            $pageTitle = 'Articles - ' . (string) $selectedCategory['name'];
        }

        $metaDescription = 'Parcourez les articles publies avec recherche, filtres et navigation par categorie.';
        $ogImage = '/assets/images/default-og.jpg';

        $this->render('front/articles/index', [
            'articles' => $result['items'] ?? [],
            'pagination' => $result['pagination'] ?? [],
            'categories' => $categories,
            'filters' => [
                'q' => $search,
                'category' => $categorySlug,
                'date' => $dateFilter ?? '',
            ],
            'selectedCategory' => $selectedCategory,
        ], $pageTitle, $metaDescription, $ogImage);
    }

    public function show($slug): void
    {
        $safeSlug = trim((string) $slug);
        if ($safeSlug === '') {
            $this->notFound('Article not found.');
        }

        $article = $this->articleModel->findBySlug($safeSlug);
        if (!$this->isPubliclyVisible($article)) {
            $this->notFound('Article not found.');
        }

        $related = [];
        $categoryId = (int) ($article['category_id'] ?? 0);
        if ($categoryId > 0) {
            $relatedPage = $this->articleModel->searchPaginated('%', 1, 6, 'published', $categoryId);
            foreach (($relatedPage['items'] ?? []) as $candidate) {
                if ((int) ($candidate['id'] ?? 0) === (int) ($article['id'] ?? 0)) {
                    continue;
                }

                if (!$this->isPubliclyVisible($candidate)) {
                    continue;
                }

                $related[] = $candidate;
                if (count($related) >= 3) {
                    break;
                }
            }
        }

        $title = (string) ($article['title'] ?? 'Article');
        $pageTitle = trim((string) ($article['meta_title'] ?? ''));
        if ($pageTitle === '') {
            $pageTitle = $title;
        }

        $metaDescription = trim((string) ($article['meta_description'] ?? ''));
        if ($metaDescription === '') {
            $metaDescription = trim((string) ($article['excerpt'] ?? ''));
        }
        if ($metaDescription === '') {
            $metaDescription = mb_substr(strip_tags((string) ($article['content'] ?? '')), 0, 160);
        }

        $ogImage = '/assets/images/default-og.jpg';
        $rawImage = trim((string) ($article['image'] ?? ''));
        if ($rawImage !== '') {
            $ogImage = str_starts_with($rawImage, '/') ? $rawImage : '/' . ltrim($rawImage, '/');
        }

        $this->render('front/articles/show', [
            'article' => $article,
            'relatedArticles' => $related,
        ], $pageTitle, $metaDescription, $ogImage);
    }

    public function byCategory($slug): void
    {
        $safeSlug = trim((string) $slug);
        if ($safeSlug === '') {
            $this->notFound('Category not found.');
        }

        $category = $this->categoryModel->findBySlug($safeSlug);
        if ($category === null || (string) ($category['status'] ?? 'hidden') !== 'active') {
            $this->notFound('Category not found.');
        }

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 9;
        $search = trim((string) ($_GET['q'] ?? ''));
        $dateFilter = $this->normalizeDateFilter($_GET['date'] ?? null);

        $categoryId = (int) ($category['id'] ?? 0);
        $result = $this->queryPublishedArticles($search, $page, $perPage, $categoryId);

        if ($dateFilter !== null) {
            $result = $this->applyDateFilterToPaginatedQuery(
                $dateFilter,
                $page,
                $perPage,
                function (int $sourcePage, int $sourcePerPage) use ($search, $categoryId): array {
                    return $this->queryPublishedArticles($search, $sourcePage, $sourcePerPage, $categoryId);
                }
            );
        }

        $pageTitle = trim((string) ($category['seo_title'] ?? ''));
        if ($pageTitle === '') {
            $pageTitle = 'Articles - ' . (string) ($category['name'] ?? 'Categorie');
        }

        $metaDescription = trim((string) ($category['seo_description'] ?? ''));
        if ($metaDescription === '') {
            $metaDescription = trim((string) ($category['description'] ?? 'Articles de la categorie.'));
        }

        $ogImage = '/assets/images/default-og.jpg';

        $this->render('front/articles/category', [
            'category' => $category,
            'articles' => $result['items'] ?? [],
            'pagination' => $result['pagination'] ?? [],
            'filters' => [
                'q' => $search,
                'date' => $dateFilter ?? '',
            ],
        ], $pageTitle, $metaDescription, $ogImage);
    }

    private function queryPublishedArticles(string $search, int $page, int $perPage, ?int $categoryId): array
    {
        if ($search !== '' || $categoryId !== null) {
            $safeSearch = $search !== '' ? $search : '%';

            return $this->articleModel->searchPaginated($safeSearch, $page, $perPage, 'published', $categoryId);
        }

        return $this->articleModel->listPaginated($page, $perPage, 'published');
    }

    private function applyDateFilterToPaginatedQuery(string $dateFilter, int $page, int $perPage, callable $query): array
    {
        $sourcePage = 1;
        $sourcePerPage = 100;

        $matchCount = 0;
        $visibleItems = [];
        $startIndex = (($page - 1) * $perPage) + 1;
        $endIndex = $startIndex + $perPage - 1;

        do {
            $result = $query($sourcePage, $sourcePerPage);
            $sourceItems = $result['items'] ?? [];
            $pagination = $result['pagination'] ?? [];

            foreach ($sourceItems as $article) {
                if (!$this->isPubliclyVisible($article)) {
                    continue;
                }

                $publishedDate = (string) ($article['published_at'] ?? '');
                if (!str_starts_with($publishedDate, $dateFilter)) {
                    continue;
                }

                $matchCount++;
                if ($matchCount >= $startIndex && $matchCount <= $endIndex) {
                    $visibleItems[] = $article;
                }
            }

            $hasNextSourcePage = (bool) ($pagination['has_next'] ?? false);
            $sourcePage++;
        } while ($hasNextSourcePage === true);

        $totalPages = max(1, (int) ceil($matchCount / $perPage));

        return [
            'items' => $visibleItems,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $matchCount,
                'total_pages' => $totalPages,
                'has_prev' => $page > 1,
                'has_next' => $page < $totalPages,
            ],
        ];
    }

    private function isPubliclyVisible(?array $article): bool
    {
        if ($article === null) {
            return false;
        }

        if ((string) ($article['status'] ?? '') !== 'published') {
            return false;
        }

        $publishedAt = (string) ($article['published_at'] ?? '');
        if ($publishedAt === '') {
            return false;
        }

        return strtotime($publishedAt) !== false && strtotime($publishedAt) <= time();
    }

    private function normalizeDateFilter($rawDate): ?string
    {
        $date = trim((string) $rawDate);
        if ($date === '') {
            return null;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return null;
        }

        $timestamp = strtotime($date . ' 00:00:00');
        if ($timestamp === false) {
            return null;
        }

        return date('Y-m-d', $timestamp);
    }

    private function render(
        string $view,
        array $data = [],
        string $pageTitle = 'Guerre Iran Irak',
        string $metaDescription = '',
        string $ogImage = '/assets/images/default-og.jpg'
    ): void
    {
        extract($data, EXTR_SKIP);

        $viewPath = APP_ROOT . '/views/' . $view . '.php';
        $layoutPath = APP_ROOT . '/views/front/layout.php';

        if (!is_readable($viewPath)) {
            http_response_code(500);
            header('Content-Type: text/plain; charset=UTF-8');
            echo '500 View not found';
            exit;
        }

        if (!is_readable($layoutPath)) {
            http_response_code(500);
            header('Content-Type: text/plain; charset=UTF-8');
            echo '500 Layout not found';
            exit;
        }

        ob_start();
        require $viewPath;
        $content = (string) ob_get_clean();

        require $layoutPath;
    }

    private function notFound(string $message): void
    {
        http_response_code(404);
        header('Content-Type: text/plain; charset=UTF-8');
        echo $message;
        exit;
    }
}
