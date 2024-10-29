<?php

namespace App\Model;

class UserFriend implements ModelInterface
{
    public function __construct(
        protected readonly int $userId,
        protected readonly int $friendId
    ) {}

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getFriendId(): int
    {
        return $this->friendId;
    }
}
