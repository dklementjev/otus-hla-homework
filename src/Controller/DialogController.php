<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Dialog\CreateMessage;
use App\Model\User;
use App\Utils\Model as ModelUtils;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route(name: 'dialog_', path: '/dialog')]
class DialogController extends BaseController
{
    public function __construct(
        protected readonly Security $security,
        protected readonly ModelUtils\Dialog $dialogUtils,
        SerializerInterface $serializer,
        #[Autowire(param: 'controller.default_json_encode_options')]
        int $jsonEncodeOptions
    ) {
        parent::__construct($serializer, $jsonEncodeOptions);
    }

    #[Route(name: 'pm_send', path: '/{other_user_id}/send', methods: ['POST'], requirements: ['other_user_id' => '\d+'])]
    public function sendMessageAction(
        string $other_user_id,
        #[MapRequestPayload(acceptFormat: 'json', validationFailedStatusCode: Response::HTTP_BAD_REQUEST)]
        CreateMessage $requestDto
    ): Response {
        /** @var User */
        $user = $this->security->getUser();
        $sessionUserId = $user->getId();

        $dialog = $this->dialogUtils->getOrCreatePMForUsers($sessionUserId, (int) $other_user_id);
        $message = $this->dialogUtils->createMessage($sessionUserId, $dialog->getId(), $requestDto->text);

        return new JsonResponse(
            [
                'success' => !empty($message),
            ],
            empty($message) ? Response::HTTP_BAD_REQUEST : Response::HTTP_OK
        );
    }

    #[Route(name: 'pm_list', path: '/{other_user_id}/list', methods: ['GET'], requirements: ['other_user_id' => '\d+'])]
    public function listDialogMessagesAction(string $other_user_id): Response
    {
        /** @var User */
        $user = $this->security->getUser();
        $sessionUserId = $user->getId();

        $serializer = $this->serializer;
        $dialog = $this->dialogUtils->getOrCreatePMForUsers($sessionUserId, (int) $other_user_id);
        $dialogMessages = $this->dialogUtils->getRecentMessages($dialog->getId());

        return new JsonResponse(
            $serializer->serialize($dialogMessages, 'json'),
            json: true
        );
    }
}
