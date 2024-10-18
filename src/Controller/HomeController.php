<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController
{
    #[Route(path: '/', name: 'home')]
    public function indexAction(): Response
    {
        return new Response(
            'OK',
            Response::HTTP_OK,
            [
                'content-type' => "text/plain",
            ]
        );
    }
}
