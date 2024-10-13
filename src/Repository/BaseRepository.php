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

    protected function hydrateAll(array $rawDataArray): array
    {
        return array_filter(
            array_map(
                fn($rawData) => $this->hydrate($rawData),
                $rawDataArray
            )
        );
    }

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

    protected function beginTransaction(bool $isMaster = false): bool
    {
        return $this->getConnection($isMaster)->beginTransaction();
    }

    protected function commitTransaction(bool $isMaster = false): bool
    {
        return $this->getConnection($isMaster)->commit();
    }

    protected function rollbackTransaction(bool $isMaster = false): bool
    {
        return $this->getConnection($isMaster)->rollBack();
    }
}
