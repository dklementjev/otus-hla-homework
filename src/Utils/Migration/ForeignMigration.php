<?php

namespace App\Utils\Migration;

use Doctrine\Bundle\MigrationsBundle\Tests\Fixtures\Migrations\ContainerAwareMigration;
use Doctrine\DBAL\Connection;

abstract class ForeignMigration extends ContainerAwareMigration
{
    protected function addSql(string $sql, array $params = [], array $types = [],): void
    {
        throw new \LogicException("Default addSql is prohibited here");
    }

    protected function getConnection(): Connection
    {
        $rc = new \ReflectionClass($this);
        $attr = $rc->getAttributes(UseConnection::class);
        if (empty($attr)) {
            throw new \LogicException("UseConnection attribute is missing");
        }
        /** @var UseConnection $attrInstance */
        $attrInstance = $attr[0]->newInstance();
        $serviceId = sprintf('doctrine.dbal.%s_connection', $attrInstance->connection);
        $res = $this->getContainer()->get($serviceId);
        if (!$res instanceof Connection) {
            throw new \LogicException("{$serviceId} is not an instance of Connection");
        }

        return $res;
    }
}
