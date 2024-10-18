<?php

namespace App\Utils\Migration;

#[\Attribute(\Attribute::TARGET_CLASS)]
class UseConnection
{
    public function __construct(
        public readonly string $connection
    ) {
    }
}
