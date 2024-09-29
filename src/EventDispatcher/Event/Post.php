<?php

declare(strict_types=1);

namespace App\EventDispatcher\Event;

use App\Model;

readonly class Post
{
    public function __construct(
        protected Model\Post $post
    ) {}

    public function getPost(): Model\Post
    {
        return $this->post;
    }
}