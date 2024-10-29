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
    ) {}

    #[Route(name: 'index', path: '/')]
    public function indexAction(): Response
    {
        $data = [];

        return new Response(
            $this->twig->render(
                'ui/index.html.twig',
                $data
            )
        );
    }

    #[Route(name: 'auth_login', path: '/login')]
    public function loginAction()
    {
        $form = $this->createForm(Login::class);

        $data = [
            'form' => $form->createView(),
        ];

        return new Response(
            $this->twig->render(
                'ui/auth/login.html.twig',
                $data
            )
        );
    }
}
