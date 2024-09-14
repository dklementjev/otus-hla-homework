<?php

namespace App\Model;

use Ramsey\Uuid\UuidInterface;

class Post implements ModelInterface
{
    public function __construct(
        protected readonly ?int $id,
        protected readonly int $userId,
        protected readonly UuidInterface $uuid,
        protected ?string $text = null
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getUUID(): UuidInterface
    {
        return $this->uuid;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $value): self
    {
        $this->text = $value;

        return $this;
    }
}