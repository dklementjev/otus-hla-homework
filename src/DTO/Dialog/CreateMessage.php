<?php

namespace App\DTO\Dialog;

final class CreateMessage
{
    public function __construct(
        public readonly string $text
    ) {}
}
