<?php

namespace App\DTO\User;

use Symfony\Component\Validator\Constraints as Assert;

readonly class RegisterRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public string $first_name,    
        #[Assert\NotBlank]
        public string $second_name,    
        #[Assert\NotBlank]
        #[Assert\Date()]
        public string $birthdate,    
        #[Assert\NotBlank]
        public string $biography,    
        #[Assert\NotBlank]
        public string $city,    
        #[Assert\NotBlank]
        #[Assert\PasswordStrength(minScore: 2)]
        public string $password
    ) {}
}