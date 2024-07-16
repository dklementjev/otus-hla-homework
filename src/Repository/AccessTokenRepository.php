<?php

namespace App\Repository;

use App\Model\AccessToken;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid;

class AccessTokenRepository
{
    public function __construct(
        protected readonly Connection $dbConnection
    ) {}

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
            $this->dbConnection->fetchAssociative($sql, ['id'=>$id]) ?: null
        );
    }

    public function getByRawToken(string $accessToken): ?AccessToken
    {
        $sql = 'SELECT * FROM app_access_tokens WHERE token=:access_token';

        return $this->hydrate(
            $this->dbConnection->fetchAssociative($sql, ['access_token'=>$accessToken]) ?: null
        );
    }

    public function insert(AccessToken $accessToken): AccessToken
    {
        $sql = 'INSERT INTO app_access_tokens (user_id, token) VALUES (:user_id, :token)';
        $this->dbConnection->executeQuery(
            $sql, 
            [
                'user_id' => $accessToken->getUserId(), 
                'token' => $accessToken->getRawToken()
            ]
        );
        $id = $this->dbConnection->lastInsertId();

        return $this->getById($id);
    }

    protected function hydrate(?array $rawData): ?AccessToken
    {
        if (!$rawData) {
            return null;
        }

        return (new AccessToken($rawData['id'] ?? null))
            ->setUserId($rawData['user_id'])
            ->setRawToken($rawData['token']) ;        
    }
}