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

        $view = APP_ROOT . '/views/front/about.php';
        require APP_ROOT . '/views/front/layout.php';
    }
}
