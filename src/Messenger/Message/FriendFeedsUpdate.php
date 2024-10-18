<?php

namespace App\Messenger\Message;

readonly class FriendFeedsUpdate
{
    public function __construct(
        protected int $userId
    ) {
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
}
