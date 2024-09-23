<?php

declare(strict_types=1);

namespace App\Utils\Model;

use App\DTO;
use App\Model\Post as ModelPost;
use App\Repository\PostRepository;
use Ramsey\Uuid\UuidInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class Post
{
    public function __construct(
        protected readonly PostRepository $postRepository,
        protected readonly CacheInterface $feedCache,
        protected readonly int $feedCacheLifetime
    ) {}

    public function createFromDTO(int $userId, DTO\Post\CreatePost $dto): ModelPost
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
        return $this->postRepository->update($post);
    }

    public function insert(ModelPost $post): ModelPost
    {
        return $this->postRepository->insert($post);
    }

    public function upsert(ModelPost $post): ModelPost
    {
        return $this->postRepository->upsert($post);
    }

    public function delete(ModelPost $post): bool
    {
        return $this->postRepository->delete($post);
    }

    public function getFeed(int $userId): array
    {
        $cacheKey = $this->generateCacheKey(['feed', (string) $userId]);
        $cacheLifetime = $this->feedCacheLifetime;
        $postRepository = $this->postRepository;

        return $this->feedCache->get(
            $cacheKey,
            function (ItemInterface $item) use ($cacheLifetime, $postRepository, $userId)  {
                //TODO: lock ?
                $posts = $postRepository->findFeedPostsForUser($userId);
                $postDTOs = array_map(
                    static fn (ModelPost $post) => DTO\Post\Post::createFromModel($post),
                    $posts
                );
                $item->expiresAfter($cacheLifetime);

                return $postDTOs;
            }
        );
    }

    public function getByUUID(UuidInterface $uuid): ?ModelPost
    {
        return $this->postRepository->getByUUID($uuid);
    }

    /**
     * @param string[] $suffixBits
     */
    protected function generateCacheKey(array $suffixBits): string
    {
        $bits = [
            $this->getCacheKeyPrefix(),
            ...$suffixBits
        ];

        return join('__', $bits);
    }

    protected function getCacheKeyPrefix(): string
    {
        return 'utils__post';
    }
}