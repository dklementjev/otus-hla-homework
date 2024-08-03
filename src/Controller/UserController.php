<?php

namespace App\Controller;

use App\DTO;
use App\Model\User;
use App\Utils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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
        
        return new JsonResponse(DTO\User\GetByIdResponse::fromModel($user));
    }

    #[Route(path: '/search', methods: ['GET'], name: 'search')]
    public function search(Request $request): Response
    {
        $firstName = $request->query->get("first_name");
        $lastName = $request->query->get("last_name");

        if (empty($firstName) || empty($lastName)) {
            throw new BadRequestHttpException("Either first or last name is empty");
        }
        $users = $this->userUtils->findByNamePrefix($firstName, $lastName);

        $jsonItems = array_map(static fn (User $user) => DTO\User\User::fromModel($user), $users);

        $json = [
            'items' => $jsonItems,
        ];

        return new JsonResponse($json);
    }
}