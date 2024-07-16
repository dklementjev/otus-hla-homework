<?php

namespace App\DTO\Auth;

use SensitiveParameter;

readonly class LoginRequest
{
    public function __construct(
        public string $id, 
        #[SensitiveParameter]
        public string $password
    ) {}
}