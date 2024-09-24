<?php

declare(strict_types=1);

namespace App\EventDispatcher\EventType;

enum Post: string
{
    case Create = 'post.create';
    case Insert = 'post.insert';
    case Update = 'post.update';
    case Delete = 'post.delete';
}
