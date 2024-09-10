<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\UserFriend;
use Doctrine\DBAL\Connection;

class UserFriendRepository
{
    protected Connection $rwConnection;

    protected Connection $roConnection;

    public function __construct(
        Connection $dbConnection,
        Connection $slaveConnection
    ) {
        $this->rwConnection = $dbConnection;
        $this->roConnection = $slaveConnection;
    }

    public function getByUserIdAndFriendId(int $userId, int $friedndId): ?UserFriend
    {
        $sql = <<<'SQL'
        SELECT uf.*
            FROM app_user_friends AS uf
            WHERE user_id=:user_id AND friend_id=:friend_id
SQL;
        return $this->hydrate(
            $this->roConnection->fetchAssociative(
                $sql,
                ['user_id' => $userId, 'friend_id' => $friedndId]
            )
        );
    }

    public function deleteByUserIdAndFriendId(int $userId, int $friedndId): int
    {
        $sql = <<<'SQL'
        DELETE FROM app_user_friends AS uf
            WHERE user_id=:user_id AND friend_id=:friend_id
SQL;
        return (int) $this->rwConnection->executeStatement(
            $sql,
            [
                'user_id' => $userId,
                'friend_id' => $friedndId,
            ]
            );
    }

    public function addFriendId(int $userId, int $friendId): int
    {
        $sql = <<<'SQL'
        INSERT INTO app_user_friends(user_id, friend_id)
            VALUES (:user_id, :friend_id)
            ON CONFLICT DO NOTHING
SQL;
        return (int) $this->rwConnection->executeStatement(
            $sql,
            [
                'user_id' => $userId,
                'friend_id' => $friendId
            ]
        );
    }

    protected function hydrate(array|bool $rawData): ?UserFriend
    {
        if ($rawData === false) {
            return null;
        }

        return new UserFriend(
            $rawData['user_id'],
            $rawData['friend_id']
        );
    }
}