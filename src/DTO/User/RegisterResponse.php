<?php

namespace App\DTO\User;

final readonly class RegisterResponse
{
    public function __construct(
        public string $user_id
    ) {}
}