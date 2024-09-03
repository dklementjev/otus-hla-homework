<?php

namespace App\Repository;

use App\Model\User;
use Doctrine\DBAL\Connection;

class UserRepository
{
    protected readonly Connection $roConnection;

    protected readonly Connection $rwConnection;

    public function __construct(
        Connection $dbConnection,
        Connection $slaveConnection
    ) {
        $this->rwConnection = $dbConnection;
        $this->roConnection = $slaveConnection;
    }

    public function create(): User
    {
        return new User(null);
    }

    public function getById(int $userId): ?User
    {
        $sql = 'SELECT * FROM app_users AS u WHERE u.id=:user_id';

        return $this->hydrate(
            $this->roConnection->fetchAssociative($sql, ['user_id' => $userId ]) ?: null
        );
    }

    public function insert(User $user): ?User
    {
        $sql = <<<'SQL'
INSERT INTO app_users(first_name, last_name, birthdate, bio, city, pass) 
VALUES (:first_name, :last_name, :birthdate, :bio, :city, :password_hash)
SQL;

        $this->rwConnection
            ->executeStatement(
                $sql, 
                [
                    'first_name' => $user->getFirstName(),
                    'last_name' => $user->getLastName(),
                    'birthdate' => $user->getBirthdate()->format('Y-m-d'),
                    'bio' => $user->getBio(),
                    'city' => $user->getCity(),
                    'password_hash' => $user->getPasswordHash()
                ]
            )
        ;
        $userId = $this->rwConnection->lastInsertId();

        return $userId ? $this->getById((int) $userId) : null;
    }

    public function count(): int
    {
        $sql = <<<'SQL'
SELECT COUNT(id) FROM app_users
SQL;
        $sth = $this->roConnection->executeQuery($sql);
        /** @var false|array{0: int} */
        $row = $sth->fetchNumeric();

        return $row ? (int) $row[0] : 0;
    }

    /**
     * @return User[]
     */
    public function findByNamePrefix(string $firstNamePrefix, string $lastNamePrefix): array
    {
        $res = [];

        $sql = <<<'SQL'
        SELECT * FROM app_users AS u WHERE 
            starts_with(lower(u.first_name), :first_name::text) AND 
            starts_with(lower(u.last_name), :last_name::text)
        ORDER BY u.id
SQL;
        $sth = $this->roConnection->executeQuery(
            $sql, 
            [
                'first_name' => mb_strtolower($firstNamePrefix), 
                'last_name' => mb_strtolower($lastNamePrefix),
            ]
        );
        while ($row = $sth->fetchAssociative()) {
            $res[] = $this->hydrate($row);
        }

        return $res;
    }

    /**
     * @param array{id: int, first_name: string, last_name: string, bio: string, birthdate: string, city: string, pass: string} $rawData
     */
    protected function hydrate(array $rawData): User
    {
        return (new User($rawData['id']))
            ->setFirstName($rawData['first_name'])
            ->setLastName($rawData['last_name'])
            ->setBirthDate(new \DateTimeImmutable($rawData['birthdate'], new \DateTimeZone('UTC')))
            ->setBio($rawData['bio'])
            ->setCity($rawData['city'])
            ->setPasswordHash($rawData['pass'])
        ;
    }
}