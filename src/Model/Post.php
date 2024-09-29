<?php

namespace App\Model;

use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

class Post implements ModelInterface
{
    public function __construct(
        protected readonly ?int $id,
        protected readonly int $userId,
        #[Groups(['default_view'])]
        #[SerializedName('id')]
        protected readonly UuidInterface $uuid,
        #[Groups(['default_view'])]
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