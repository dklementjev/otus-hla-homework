<?php

namespace App\Repository;

use App\Model\User;

/**
 * @phpstan-type RawUser array{id: int, first_name: string, last_name: string, birthdate: string, bio: string, city: string, pass: string}
 *
 * @template-extends BaseRepository<RawUser, User>
 */
class UserRepository extends BaseRepository
{
    public function create(): User
    {
        return new User(null);
    }

    public function getById(int $userId): ?User
    {
        $sql = 'SELECT * FROM app_users AS u WHERE u.id=:user_id';

        return $this->hydrate(
            $this->getConnection()->fetchAssociative($sql, ['user_id' => $userId ])
        );
    }

    public function insert(User $user): ?User
    {
        $sql = <<<'SQL'
INSERT INTO app_users(first_name, last_name, birthdate, bio, city, pass)
VALUES (:first_name, :last_name, :birthdate, :bio, :city, :password_hash)
SQL;

        $this->getConnection(true)
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
        $userId = $this->getConnection(true)->lastInsertId();

        return $userId ? $this->getById((int) $userId) : null;
    }

    public function count(): int
    {
        $sql = <<<'SQL'
SELECT COUNT(id) FROM app_users
SQL;
        $sth = $this->getConnection()->executeQuery($sql);
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
        $sth = $this->getConnection()->executeQuery(
            $sql,
            [
                'first_name' => mb_strtolower($firstNamePrefix),
                'last_name' => mb_strtolower($lastNamePrefix),
            ]
        );
        while ($row = $sth->fetchAssociative()) {
            $res[] = $this->hydrate($row);
        }

        return array_filter($res);
    }

    protected function hydrate(bool|array $rawData): ?User
    {
        if ($this->isEmptyRawData($rawData)) {
            return null;
        }

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