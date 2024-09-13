<?php

namespace App\Repository;

use App\Model\ModelInterface;

/**
 * @phpstan-template RawType of array
 * @phpstan-template ModelType of ModelInterface
 */
abstract class BaseRepository
{
    /**
     * @param false|RawType $rawData
     *
     * @return ModelType
     */
    abstract protected function hydrate(bool|array $rawData): ?ModelInterface;

    /**
     * @param false|RawType $rawData
     *
     * @phpstan-assert-if-false RawType $rawData
     */
    protected function isEmptyRawData(false|array $rawData): bool
    {
        return ($rawData === false);
    }
}