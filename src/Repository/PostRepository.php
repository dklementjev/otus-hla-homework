<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Post;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @phpstan-type RawPost array{id: int, user_id: int, uuid: string, text: string}
 *
 * @template-extends BaseRepository<RawPost, Post>
 */
class PostRepository extends BaseRepository
{
    public function create(int $userId): Post
    {
        return new Post(null, $userId, Uuid::uuid7());
    }

    public function upsert(Post $model): Post
    {
        if ($this->countByUUID($model->getUUID()) > 0) {
            $this->update($model);
        } else {
            $this->insert($model);
        }

        return $model;
    }

    public function countByUUID(UuidInterface $uuid): int
    {
        $sql = <<<'SQL'
        SELECT COUNT(*) AS count FROM app_posts WHERE "uuid"=:uuid
SQL;

        return (int) $this->getConnection()->fetchOne($sql, ['uuid'=>(string) $uuid]);
    }

    public function getByUUID(UuidInterface $uuid, bool $useMaster = false): ?Post
    {
        $sql = <<<'SQL'
        SELECT * FROM app_posts WHERE "uuid"=:uuid
SQL;
        /** @var false|RawPost */
        $rawPost = $this->getConnection($useMaster)->fetchAssociative(
            $sql,
            [
                'uuid' => (string) $uuid,
            ]
        );

        return $this->hydrate($rawPost);
    }

    public function insert(Post $model): ?Post
    {
        $sql = <<<'SQL'
        INSERT INTO app_posts ("uuid", "user_id", "text") VALUES (:uuid, :user_id, :text)
SQL;
        $this->getConnection(true)->executeQuery(
            $sql,
            [
                'uuid' => (string) $model->getUUID(),
                'user_id' => $model->getUserId(),
                'text' => $model->getText(),
            ]
            );

        return $this->getByUUID($model->getUUID(), true);
    }

    public function update(Post $model): ?Post
    {
        $sql = <<<'SQL'
        UPDATE app_posts SET "text"=:text WHERE "uuid"=:uuid
SQL;
        $this->getConnection(true)->executeQuery(
            $sql,
            [
                'uuid' => (string) $model->getUUID(),
                'text' => $model->getText(),
            ]
            );

        return $this->getByUUID($model->getUUID(), true);
    }

    public function delete(Post $model): bool
    {
        $sql = <<<'SQL'
        DELETE FROM app_posts WHERE "uuid"=:uuid
SQL;
        $rowCount = $this->getConnection(true)->executeStatement(
            $sql,
            [
                'uuid' => (string) $model->getUUID(),
            ]
        );

        return ($rowCount > 0);
    }

    protected function hydrate(array|bool $rawData): ?Post
    {
        if ($this->isEmptyRawData($rawData)) {
            return null;
        }

        return new Post(
            $rawData['id'],
            $rawData['user_id'],
            Uuid::fromString($rawData['uuid']),
            $rawData['text']
        );
    }
}