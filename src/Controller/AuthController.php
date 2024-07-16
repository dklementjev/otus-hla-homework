<?php

namespace App\Controller;

use App\DTO\Auth\LoginRequest;
use App\DTO\Auth\LoginResponse;
use App\Repository\AccessTokenRepository;
use App\Repository\UserRepository;
use SensitiveParameter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route(name: 'auth_')]
class AuthController
{
    public function __construct(
        protected readonly UserRepository $userRepository,
        protected readonly AccessTokenRepository $accessTokenRepository,
        protected readonly UserPasswordHasherInterface $passwordHasher
    ) {}

    #[Route(path: '/login', methods: ['POST'], name: 'login')]
    public function login(
        #[SensitiveParameter]
        #[MapRequestPayload(acceptFormat: 'json', validationFailedStatusCode: Response::HTTP_BAD_REQUEST)]
        LoginRequest $loginDto
    ): Response {
        $user = $this->userRepository->getById($loginDto->id);
        if (!$user) {
            throw new NotFoundHttpException("User not found");
        }
        if (!$this->passwordHasher->isPasswordValid($user, $loginDto->password)) {
            throw new HttpException(Response::HTTP_UNAUTHORIZED);
        }

        $accessToken = $this->accessTokenRepository
            ->create()
            ->setUserId($user->getId())
        ;
        $this->accessTokenRepository->insert($accessToken);
        
        $dto = new LoginResponse($accessToken->getRawToken());

        return new JsonResponse(
            $dto
        );
    }
}