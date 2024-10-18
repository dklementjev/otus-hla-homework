<?php

namespace App\DTO\Auth;

use SensitiveParameter;

final readonly class LoginRequest
{
    public function __construct(
        public string $id,
        #[SensitiveParameter]
        public string $password
    ) {
    }
}
