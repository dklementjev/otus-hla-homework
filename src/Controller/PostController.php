<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO;
use App\Utils\Model\Post;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route(name: 'post_', path: '/post')]
class PostController
{
    public function __construct(
        protected readonly Security $security,
        protected readonly Post $postUtils
    ) {}

    #[Route(name: 'create', path: '/create', methods: ['POST'])]
    public function createAction(
        #[MapRequestPayload(acceptFormat:'json', validationFailedStatusCode: Response::HTTP_BAD_REQUEST)] DTO\Post\CreatePost $requestDto
    ): Response {
        /** @var User */
        $user = $this->security->getUser();
        $userId = $user->getId();

        $post = $this->postUtils->createFromDto($userId, $requestDto);
        $this->postUtils->update($post);

        return new JsonResponse(
            DTO\Post\Post::createFromModel($post)
        );
    }

    #[Route(name: 'get', path: '/get/{id}', methods: ['GET'], requirements: ['post_id' => DTO\Post\PostId::REGEX])]
    public function getAction(
        string $id
    ): Response {
        $postUUID = Uuid::fromString($id);
        $post = $this->postUtils->getByUUID($postUUID);
        if ($post === null) {
            throw new BadRequestException('Invalid post id');
        }

        return new JsonResponse(
            DTO\Post\Post::createFromModel($post)
        );
    }

    #[Route(name: 'update', path: '/update', methods: ['PUT'])]
    public function updateAction(
        #[MapRequestPayload(acceptFormat:'json', validationFailedStatusCode: Response::HTTP_BAD_REQUEST)] DTO\Post\UpdatePost $requestDto
    ): Response {
        $post = $this->postUtils->getByUUID($requestDto->uuid);
        if ($post === null) {
            throw new BadRequestException('Invalid post id');
        }
        $post->setText($requestDto->text);
        $post = $this->postUtils->update($post);

        return new JsonResponse(
            DTO\Post\Post::createFromModel($post)
        );
    }

    #[Route(name: 'delete', path: '/delete/{id}', methods: ['PUT'], requirements: ['post_id' => DTO\Post\PostId::REGEX])]
    public function deleteAction(
        string $id
    ): Response {
        $postUUID = Uuid::fromString($id);
        $post = $this->postUtils->getByUUID($postUUID);
        if ($post === null) {
            throw new BadRequestException('Invalid post id');
        }

        $this->postUtils->delete($post);

        return new JsonResponse([
            'success' => true,
        ]);
    }
}