<?php

namespace App\Messenger\Message;

readonly class UserNotification
{
    public function __construct(
        protected int    $userId,
        protected string $command,
        protected ?array $data
    ) {}

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function getData(): ?array
    {
        return $this->data;
    }
}
