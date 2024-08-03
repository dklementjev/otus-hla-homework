<?php

namespace App\DTO\User;

use App\Model;

final readonly class User
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $first_name,
        public readonly string $second_name,
        public readonly string $birthdate,
        public readonly string $biography,
        public readonly string $city
    ) { }

    public static function fromModel(Model\User $user): self 
    {
        return new static(
            $user->getId(),
            $user->getFirstName(),
            $user->getLastName(),
            $user->getBirthdate()->format('Y-m-d'),
            $user->getBio(),
            $user->getCity()
        );
    }
}