<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240802215939 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Bare index - parallel seq scan with ILIKE';
    }

    public function up(Schema $schema): void
    {
        $sql = <<<'SQL'
        CREATE INDEX idx_user_fullname ON app_users (first_name varchar_pattern_ops, last_name varchar_pattern_ops)
SQL;
        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $sql = <<<'SQL'
        DROP INDEX idx_user_fullname
SQL;
        $this->addSql($sql);
    }
}
