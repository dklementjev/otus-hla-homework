<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Post;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class PostRepository
{
    protected Connection $roConnection;

    protected Connection $rwConnection;

    public function __construct(
        Connection $dbConnection,
        Connection $slaveConnection
    ) {
        $this->rwConnection = $dbConnection;
        $this->roConnection = $slaveConnection;
    }

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

$count = (int) $this->roConnection->fetchOne($sql, ['uuid'=>(string) $uuid]);

        return $count;
    }

    public function getByUUID(UuidInterface $uuid, bool $useMaster = false): ?Post
    {
        $connection = $useMaster ? $this->rwConnection : $this->roConnection;

        $sql = <<<'SQL'
        SELECT * FROM app_posts WHERE "uuid"=:uuid
SQL;
        $rawPost = $connection->fetchAssociative(
            $sql,
            [
                'uuid' => (string) $uuid,
            ]
        );

        return $this->hydrate($rawPost);
    }

    public function insert(Post $model): Post
    {
        $sql = <<<'SQL'
        INSERT INTO app_posts ("uuid", "user_id", "text") VALUES (:uuid, :user_id, :text)
SQL;
        $this->rwConnection->executeQuery(
            $sql,
            [
                'uuid' => (string) $model->getUUID(),
                'user_id' => $model->getUserId(),
                'text' => $model->getText(),
            ]
            );

        return $this->getByUUID($model->getUUID(), true);
    }

    public function update(Post $model): Post
    {
        $sql = <<<'SQL'
        UPDATE app_posts SET "text"=:text WHERE "uuid"=:uuid
SQL;
        $this->rwConnection->executeQuery(
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
        $rowCount = $this->rwConnection->executeStatement(
            $sql,
            [
                'uuid' => (string) $model->getUUID(),
            ]
        );

        return ($rowCount > 0);
    }

    protected function hydrate(array|false $rawData): ?Post
    {
        if ($rawData === false) {
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