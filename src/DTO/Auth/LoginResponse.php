<?php

namespace App\DTO\Auth;

final readonly class LoginResponse
{
    public function __construct(
        public string $token
    ) {}
}