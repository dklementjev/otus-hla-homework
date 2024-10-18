<?php

namespace App\Repository;

#[\Attribute(\Attribute::TARGET_CLASS)]
class ModelClass
{
    public function __construct(
        public readonly string $name
    ) {
    }
}
