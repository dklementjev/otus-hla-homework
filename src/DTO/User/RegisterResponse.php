<?php

namespace App\DTO\User;

readonly class RegisterResponse
{
    public function __construct(
        public string $user_id
    ) {}
}