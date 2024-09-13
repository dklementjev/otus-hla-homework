<?php

namespace App\Repository;

use App\Model\ModelInterface;
use Doctrine\DBAL\Connection;

/**
 * @phpstan-template RawType of array
 * @phpstan-template ModelType of ModelInterface
 */
abstract class BaseRepository
{
    private readonly Connection $roConnection;

    private readonly Connection $rwConnection;

    public function __construct(
        Connection $dbConnection,
        Connection $slaveConnection
    ) {
        $this->rwConnection = $dbConnection;
        $this->roConnection = $slaveConnection;
    }

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

    protected function getConnection(bool $isMaster = false): Connection
    {
        return $isMaster ? $this->rwConnection : $this->roConnection;
    }
}