<?php

namespace App\DTO\User;

use App\Model;

readonly class GetByIdResponse
{
    public function __construct(
        public ?int $id,
        public string $first_name,
        public string $second_name,
        public string $birthdate,
        public string $biography,
        public string $city
    ) {}

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