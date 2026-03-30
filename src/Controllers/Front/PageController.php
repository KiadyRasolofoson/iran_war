<?php

declare(strict_types=1);

namespace App\Controllers\Front;

final class PageController
{
    public function about(): void
    {
        $pageTitle = 'A propos - Guerre Iran Irak';
        $metaDescription = 'Presentation du projet editorial, de la methode de verification et de la ligne de publication.';
        $ogImage = '/assets/images/default-og.jpg';

        $this->render('front/about', [], $pageTitle, $metaDescription, $ogImage);
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
