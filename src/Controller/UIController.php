<?php

namespace App\Controller;

use App\Form\Login;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment;

#[Route(name: 'ui_', path: '/ui')]
class UIController extends AbstractController
{
    public function __construct(
        protected readonly Environment $twig,
        protected string $wsApiBaseUrl,
    ) {}

    #[Route(name: 'index', path: '/')]
    public function indexAction(): Response
    {
        $loginForm = $this->createForm(Login::class);

        $data = [
            'login_form' => $loginForm->createView(),
            'ws_api_base_url' => $this->wsApiBaseUrl,
        ];

        return new Response(
            $this->twig->render(
                'ui/index.html.twig',
                $data
            )
        );
    }
}
