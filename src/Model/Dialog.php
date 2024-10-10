<?php

namespace App\Model;

use Ramsey\Uuid\UuidInterface;

class Dialog implements ModelInterface
{
    public function __construct(
        protected readonly ?int $id,
        protected readonly UuidInterface $uuid,
        protected readonly \DateTimeInterface $createdAt,
        protected readonly bool $isGroupchat
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function isGroupchat(): bool
    {
        return $this->isGroupchat;
    }
}
