<?php

namespace App\Utils\Model;

use App\DTO;
use App\Model;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class User
{
    public function __construct(
        protected readonly UserRepository $userRepository,
        protected readonly UserPasswordHasherInterface $passwordHasher
    ) {}

    public function register(
        DTO\User\RegisterRequest $dto
    ): ?Model\User {

        $res = $this->userRepository->create();
        $res->setFirstName($dto->first_name)
            ->setLastName($dto->second_name)
            ->setBirthdate(new \DateTimeImmutable($dto->birthdate, new \DateTimeZone("UTC")))
            ->setBio($dto->biography)
            ->setCity($dto->city)
            ->setPasswordHash($this->passwordHasher->hashPassword($res, $dto->password))
        ;
        return $this->userRepository->insert($res);
    }

    public function getById(int $userId): ?Model\User
    {
        return $this->userRepository->getById($userId);
    }

    public function count(): int
    {
        return $this->userRepository->count();
    }

    /**
     * @return Model\User[]
     */
    public function findByNamePrefix(string $firstNamePrefix, string $lastNamePrefix): array
    {
        return $this->userRepository->findByNamePrefix($firstNamePrefix, $lastNamePrefix);
    }

    /**
     * @return list<int>
     */
    public function pickRandomUserIds(int $count): array
    {
        return $this->userRepository->pickRandomUserIds($count);
    }
}