<?php

namespace App\Model;

class DialogParticipant implements ModelInterface
{
    public function __construct(
        protected readonly int $dialogId,
        protected readonly int $userId
    ) {
    }

    public function getDialogId(): int
    {
        return $this->dialogId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
}
