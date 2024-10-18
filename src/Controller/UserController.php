<?php

namespace App\Controller;

use App\DTO;
use App\Utils;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route(path: '/user', name: 'user_')]
class UserController extends BaseController
{
    public function __construct(
        protected readonly Utils\Model\User $userUtils,
        SerializerInterface $serializer,
        #[Autowire(param: 'controller.default_json_encode_options')]
        int $jsonEncodeOptions
    ) {
        parent::__construct($serializer, $jsonEncodeOptions);
    }

    #[Route(path: '/register', methods: ['POST'], name: 'register')]
    public function register(
        #[MapRequestPayload(acceptFormat:'json', validationFailedStatusCode: Response::HTTP_BAD_REQUEST)] DTO\User\RegisterRequest $requestDto
    ): Response {
        $user = $this->userUtils->register($requestDto);

        // FIXME: This is the only place where user_id field name is used
        return new JsonResponse(
            $this->jsonSerialize(['user_id' => $user->getId()], []),
            json: true
        );
    }

    #[Route(path: '/get/{id}', methods: ['GET'], name: 'get', requirements: ['id' => '\d+'])]
    public function getById(int $id): Response
    {
        $user = $this->userUtils->getById($id);
        if (empty($user)) {
            throw new NotFoundHttpException("User not found");
        }

        return new JsonResponse(
            $this->jsonSerialize($user, 'default_view'),
            json: true
        );
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

        return new JsonResponse(
            $this->jsonSerialize(
                ['items2' => $users],
                'default_view'
            ),
            json: true
        );
    }
}
