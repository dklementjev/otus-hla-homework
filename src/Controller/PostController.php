<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO;
use App\Model\User;
use App\Utils\Model\Post;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route(name: 'post_', path: '/post')]
class PostController extends BaseController
{
    public function __construct(
        protected readonly Security $security,
        protected readonly Post $postUtils,
        SerializerInterface $serializer,
        #[Autowire(param: 'controller.default_json_encode_options')]
        int $jsonEncodeOptions
    ) {
        parent::__construct($serializer, $jsonEncodeOptions);
    }

    #[Route(name: 'create', path: '/create', methods: ['POST'])]
    public function createAction(
        #[MapRequestPayload(acceptFormat: 'json', validationFailedStatusCode: Response::HTTP_BAD_REQUEST)] DTO\Post\CreatePost $requestDto
    ): Response {
        /** @var User */
        $user = $this->security->getUser();
        /** @var int */
        $userId = $user->getId();

        $post = $this->postUtils->createFromDto($userId, $requestDto);
        $this->postUtils->insert($post);

        return new JsonResponse(
            $this->jsonSerialize($post, 'default_view'),
            json: true
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
            $this->jsonSerialize($post, 'default_view'),
            json: true
        );
    }

    #[Route(name: 'update', path: '/update', methods: ['PUT'])]
    public function updateAction(
        #[MapRequestPayload(acceptFormat: 'json', validationFailedStatusCode: Response::HTTP_BAD_REQUEST)] DTO\Post\UpdatePost $requestDto
    ): Response {
        $post = $this->postUtils->getByUUID($requestDto->uuid);
        if ($post === null) {
            throw new BadRequestException('Invalid post id');
        }
        $post->setText($requestDto->text);
        $post = $this->postUtils->update($post);

        return new JsonResponse(
            $this->jsonSerialize($post, 'default_view'),
            json: true
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

    #[Route(name: 'feed', path: '/feed', methods: ['GET'])]
    public function feedAction(): Response
    {
        /** @var User */
        $user = $this->security->getUser();
        /** @var int */
        $userId = $user->getId();

        $posts = $this->postUtils->getFeed($userId);

        return new JsonResponse(
            $this->jsonSerialize($posts, 'default_view'),
            json: true
        );
    }
}
