<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\UserFriend;

/**
 * @phpstan-type RawUserFriend array{user_id: int, friend_id: int}
 *
 * @template-extends BaseRepository<RawUserFriend, UserFriend>
 */
#[ModelClass(UserFriend::class)]
class UserFriendRepository extends BaseRepository
{
    public function getByUserIdAndFriendId(int $userId, int $friedndId): ?UserFriend
    {
        $sql = <<<'SQL'
        SELECT uf.*
            FROM app_user_friends AS uf
            WHERE user_id=:user_id AND friend_id=:friend_id
SQL;
        /** @var false|RawUserFriend */
        $rawData = $this->getConnection()->fetchAssociative(
            $sql,
            ['user_id' => $userId, 'friend_id' => $friedndId]
        );

        return $this->hydrate($rawData);
    }

    public function deleteByUserIdAndFriendId(int $userId, int $friedndId): int
    {
        $sql = <<<'SQL'
        DELETE FROM app_user_friends AS uf
            WHERE user_id=:user_id AND friend_id=:friend_id
SQL;

        return (int) $this->getConnection(true)->executeStatement(
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

        return (int) $this->getConnection(true)->executeStatement(
            $sql,
            [
                'user_id' => $userId,
                'friend_id' => $friendId,
            ]
        );
    }

    /**
     * @return int[]
     */
    public function findFriendIdsByUserId(int $userId): array
    {
        $sql = <<<SQL
        SELECT uf.friend_id FROM app_user_friends AS uf WHERE uf.user_id=:user_id
SQL;
        $sth = $this->getConnection()->executeQuery(
            $sql,
            [
                'user_id' => $userId,
            ]
        );

        return $sth->fetchFirstColumn();
    }

    /**
     * @return int[]
     */
    public function findUserIdsByFriendId(int $userId): array
    {
        $sql = <<<SQL
        SELECT uf.user_id FROM app_user_friends AS uf WHERE uf.friend_id=:friend_id
SQL;
        $sth = $this->getConnection()->executeQuery(
            $sql,
            [
                'friend_id' => $userId,
            ]
        );

        return $sth->fetchFirstColumn();
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
