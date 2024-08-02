<?php

namespace App\Repository;

use App\Model\User;
use Doctrine\DBAL\Connection;

class UserRepository
{
    public function __construct(
        protected readonly Connection $dbConnection
    ) {}

    public function create(): User
    {
        return new User(null);
    }

    public function getById(int $userId): ?User
    {
        $sql = 'SELECT * FROM app_users AS u WHERE u.id=:user_id';

        return $this->hydrate(
            $this->dbConnection->fetchAssociative($sql, ['user_id' => $userId ]) ?: null
        );
    }

    public function insert(User $user): ?User
    {
        $sql = <<<'SQL'
INSERT INTO app_users(first_name, last_name, birthdate, bio, city, pass) 
VALUES (:first_name, :last_name, :birthdate, :bio, :city, :password_hash)
SQL;

        $this->dbConnection
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
        $userId = $this->dbConnection->lastInsertId();

        return $userId ? $this->getById((int) $userId) : null;
    }

    public function count(): int
    {
        $sql = <<<'SQL'
SELECT COUNT(id) FROM app_users
SQL;
        $sth = $this->dbConnection->executeQuery($sql);
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
            u.first_name ILIKE CONCAT(:first_name::text, '%') AND 
            u.last_name ILIKE CONCAT(:last_name::text, '%')
SQL;
        $sth = $this->dbConnection->executeQuery(
            $sql, 
            [
                'first_name' => $firstNamePrefix, 
                'last_name' => $lastNamePrefix,
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