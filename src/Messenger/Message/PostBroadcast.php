<?php

namespace App\Messenger\Message;

use Ramsey\Uuid\UuidInterface;

final readonly class PostBroadcast
{
    public function __construct(
        private int $postUserId,
        private UuidInterface $postUUID,
        private string $text
    ) {}

    public function getPostUserId(): int
    {
        return $this->postUserId;
    }

    public function getPostUUID(): UuidInterface
    {
        return $this->postUUID;
    }

    public function getText(): string
    {
        return $this->text;
    }
}
