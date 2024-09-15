<?php

declare(strict_types=1);

namespace App\Utils\Model;

use App\DTO\Post\CreatePost;
use App\Model\Post as ModelPost;
use App\Repository\PostRepository;
use Ramsey\Uuid\UuidInterface;

class Post
{
    public function __construct(
        protected readonly PostRepository $postRepository
    ) {}

    public function createFromDTO(int $userId, CreatePost $dto): ModelPost
    {
        return $this->create(
            $userId,
            $dto->text
        );
    }

    public function create(int $userId, ?string $text = null): ModelPost
    {
        return $this->postRepository
            ->create($userId)
            ->setText($text)
        ;
    }

    public function update(ModelPost $post): ModelPost
    {
        return $this->postRepository->upsert($post);
    }

    public function insert(ModelPost $post): ModelPost
    {
        return $this->postRepository->insert($post);
    }

    public function delete(ModelPost $post): bool
    {
        return $this->postRepository->delete($post);
    }

    public function getByUUID(UuidInterface $uuid): ?ModelPost
    {
        return $this->postRepository->getByUUID($uuid);
    }
}