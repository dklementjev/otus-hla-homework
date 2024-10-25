<?php

namespace App\Model;

use Ramsey\Uuid\UuidInterface;

class DialogMessage implements ModelInterface
{
    public function __construct(
        protected readonly ?int $id,
        protected readonly UuidInterface $uuid,
        protected readonly int $userId,
        protected readonly int $dialogId,
        protected string $message,
        protected readonly \DateTimeInterface $createdAt
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getDialogId(): int
    {
        return $this->dialogId;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): DialogMessage
    {
        $this->message = $message;

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }
}
