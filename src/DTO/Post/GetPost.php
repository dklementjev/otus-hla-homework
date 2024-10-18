<?php

namespace App\DTO\Post;

use App\Model\Post;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class GetPost
{
    public readonly UuidInterface $id;

    public readonly ?string $text;

    public readonly string $author_user_id;

    public function __construct(
        string $rawUUID,
        ?string $text,
        string $userId
    ) {
        $this->id = Uuid::fromString($rawUUID);
        $this->text = $text;
        $this->author_user_id = $userId;
    }

    public static function fromModel(Post $model): self
    {
        return new static(
            $model->getUUID(),
            $model->getText(),
            (string) $model->getUserId()
        );
    }
}
