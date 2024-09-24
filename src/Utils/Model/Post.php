<?php

declare(strict_types=1);

namespace App\Utils\Model;

use App\DTO;
use App\EventDispatcher\Event;
use App\EventDispatcher\EventType;
use App\Model;
use App\Repository\PostRepository;
use Psr\EventDispatcher\EventDispatcherInterface;
use Ramsey\Uuid\UuidInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class Post
{
    public function __construct(
        protected readonly PostRepository $postRepository,
        protected readonly CacheInterface $feedCache,
        protected readonly int $feedCacheLifetime,
        protected readonly EventDispatcherInterface $eventDispatcher,
    ) {}

    public function createFromDTO(int $userId, DTO\Post\CreatePost $dto): Model\Post
    {
        return $this->create(
            $userId,
            $dto->text
        );
    }

    public function create(int $userId, ?string $text = null): Model\Post
    {
        $res = $this->postRepository
            ->create($userId)
            ->setText($text)
        ;

        $this->eventDispatcher->dispatch(new Event\Post($res), EventType\Post::Create);

        return $res;
    }

    public function update(Model\Post $post): Model\Post
    {
        $res = $this->postRepository->update($post);

        $this->eventDispatcher->dispatch(new Event\Post($res), EventType\Post::Update);

        return $res;
    }

    public function insert(Model\Post $post): Model\Post
    {
        $res = $this->postRepository->insert($post);

        $this->eventDispatcher->dispatch(new Event\Post($res), EventType\Post::Insert);

        return $res;
    }

    public function delete(Model\Post $post): bool
    {
        $this->eventDispatcher->dispatch(new Event\Post($post), EventType\Post::Delete);

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
                    static fn (Model\Post $post) => DTO\Post\Post::createFromModel($post),
                    $posts
                );
                $item->expiresAfter($cacheLifetime);

                return $postDTOs;
            }
        );
    }

    public function getByUUID(UuidInterface $uuid): ?Model\Post
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