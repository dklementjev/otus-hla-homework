<?php

namespace App\DTO\Post;

use App\Model\Post as ModelPost;
use Ramsey\Uuid\UuidInterface;

final class Post
{
    public function __construct(
        public readonly UuidInterface $id,
        public readonly ?string $text
    ) {}

    public static function createFromModel(ModelPost $model): self
    {
        return new static(
            $model->getUUID(),
            $model->getText()
        );
    }
}