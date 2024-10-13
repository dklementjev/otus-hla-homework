<?php

namespace App\Model;

use Ramsey\Uuid\UuidInterface;

class DialogUser implements ModelInterface
{
    public function __construct(
        protected readonly ?int $id,
        protected readonly UuidInterface $uuid,
        protected readonly ?string $nickname
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }
}
