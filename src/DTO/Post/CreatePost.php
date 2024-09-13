<?php

namespace App\DTO\Post;

final class CreatePost
{
    public function __construct(
        public readonly string $text
    ) {}
}