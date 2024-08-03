<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240802233543 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Lowercase index';
    }

    public function up(Schema $schema): void
    {
        $sql = <<<'SQL'
        DROP INDEX idx_user_fullname
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
        CREATE INDEX idx_user_fullname_lc ON app_users (lower(first_name) varchar_pattern_ops, lower(last_name) varchar_pattern_ops)
SQL;
        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $sql = <<<'SQL'
        DROP INDEX idx_user_fullname_lc
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
        CREATE INDEX idx_user_fullname ON app_users (first_name varchar_pattern_ops, last_name varchar_pattern_ops)
SQL;
        $this->addSql($sql);
    }
}
