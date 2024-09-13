<?php

namespace App\Repository;

use App\Model\AccessToken;
use Ramsey\Uuid;

/**
 * @phpstan-type RawAccessToken array{id: null, user_id: int, token: string}
 *
 * @template-extends BaseRepository<RawAccessToken, AccessToken>
 */
class AccessTokenRepository extends BaseRepository
{
    public function create(): AccessToken
    {
        return (new AccessToken())
            ->setRawToken((string) Uuid\Uuid::uuid4())
        ;
    }

    public function getById(int $id): ?AccessToken
    {
        $sql = 'SELECT * FROM app_access_tokens WHERE id=:id';

        return $this->hydrate(
            $this->getConnection()->fetchAssociative($sql, ['id'=>$id]) ?: null
        );
    }

    public function getByRawToken(string $accessToken): ?AccessToken
    {
        $sql = 'SELECT * FROM app_access_tokens WHERE token=:access_token';

        return $this->hydrate(
            $this->getConnection()->fetchAssociative($sql, ['access_token'=>$accessToken]) ?: null
        );
    }

    public function insert(AccessToken $accessToken): ?AccessToken
    {
        $sql = 'INSERT INTO app_access_tokens (user_id, token) VALUES (:user_id, :token)';
        $this->getConnection(true)->executeQuery(
            $sql,
            [
                'user_id' => $accessToken->getUserId(),
                'token' => $accessToken->getRawToken()
            ]
        );
        /** @var int|null */
        $id = $this->getConnection(true)->lastInsertId();

        return $id === null ? null : $this->getById($id);
    }

    protected function hydrate(bool|array $rawData): ?AccessToken
    {
        if ($this->isEmptyRawData($rawData)) {
            return null;
        }

        return (new AccessToken($rawData['id']))
            ->setUserId($rawData['user_id'])
            ->setRawToken($rawData['token']);
    }
}