<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240825230102 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $sql = "CREATE TABLE test_data (id SERIAL PRIMARY KEY, value INT)";
        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $sql = "DROP TABLE test_data";
        $this->addSql($sql);
    }
}
