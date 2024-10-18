<?php

namespace App\DTO\Post;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class UpdatePost
{
    public readonly UuidInterface $uuid;

    public function __construct(
        string $id,
        public readonly string $text
    ) {
        $this->uuid = Uuid::fromString($id);
    }
}
