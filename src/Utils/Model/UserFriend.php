<?php

namespace App\Utils\Model;

use App\Model\UserFriend as UserFriendModel;
use App\Repository\UserFriendRepository;

class UserFriend
{
    public function __construct(
        protected readonly UserFriendRepository $userFriendRepository,
    ) {}

    public function getByUserIdAndFriendId(int $userId, int $friendId): ?UserFriendModel
    {
        return $this->userFriendRepository->getByUserIdAndFriendId($userId, $friendId);
    }

    public function deleteByUserIdAndFriendId(int $userId, int $friendId): int
    {
        return $this->userFriendRepository->deleteByUserIdAndFriendId($userId, $friendId);
    }

    public function addFriendById(int $userId, int $friendId): int
    {
        return $this->userFriendRepository->addFriendId($userId, $friendId);
    }

    /**
     * @return int[]
     */
    public function findFriendIdsByUserId(int $userId): array
    {
        return $this->userFriendRepository->findFriendIdsByUserId($userId);
    }

    /**
     * @return int[]
     */
    public function findUserIdsByFriendId(int $userId): array
    {
        return $this->userFriendRepository->findUserIdsByFriendId($userId);
    }
}