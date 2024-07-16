<?php

namespace App\DTO\Auth;

readonly class LoginResponse
{
    public function __construct(
        public string $token
    ) {}
}