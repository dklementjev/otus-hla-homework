<?php

namespace App\Controller;

use App\DTO;
use App\DTO\User\GetByIdResponse;
use App\Utils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/user', name: 'user_')]
class UserController
{
    public function __construct(
        protected readonly Utils\Model\User $userUtils
    ) {}

    #[Route(path: '/register', methods: ['POST'], name: 'register')]
    public function register(
        #[MapRequestPayload(acceptFormat:'json', validationFailedStatusCode: Response::HTTP_BAD_REQUEST)] DTO\User\RegisterRequest $requestDto
    ): Response {
        $user = $this->userUtils->register($requestDto);
        $userDto = new DTO\User\RegisterResponse($user->getId());

        return new JsonResponse($userDto);
    }

    #[Route(path: '/get/{id}', methods: ['GET'], name: 'get', requirements: ['id' => '\d+'])]
    public function getById(int $id): Response
    {
        $user = $this->userUtils->getById($id);
        if (empty($user)) {
            throw new NotFoundHttpException("User not found");
        }
        
        return new JsonResponse(GetByIdResponse::fromModel($user));
    }
}