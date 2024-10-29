<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\User;
use App\Utils\Model\UserFriend;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(name: 'friend_', path: '/friend')]
class FriendController
{
    public function __construct(
        protected readonly Security $security,
        protected readonly UserFriend $userFriendUtils,
    ) {}

    #[Route(name: 'add', path: '/set/{friend_id}', methods: ['PUT'], requirements: ['friend_id' => '\d+'])]
    public function addAction(Request $request): Response
    {
        /** @var User */
        $user = $this->security->getUser();
        $userId = $user->getId();
        $friendId = $request->attributes->getInt('friend_id');

        $userFriend = $this->userFriendUtils->getByUserIdAndFriendId($userId, $friendId);
        if ($userFriend === null) {
            $this->userFriendUtils->addFriendById($userId, $friendId);
        }

        return new JsonResponse([
            'success' => true,
        ]);
    }

    #[Route(name: 'delete', path: '/delete/{friend_id}', methods: ['PUT'], requirements: ['friend_id' => '\d+'])]
    public function deleteAction(Request $request): Response
    {
        /** @var User */
        $user = $this->security->getUser();
        $userId = $user->getId();
        $friendId = $request->attributes->getInt('friend_id');

        $this->userFriendUtils->deleteByUserIdAndFriendId($userId, $friendId);

        return new JsonResponse([
            'success' => true,
        ]);
    }
}
