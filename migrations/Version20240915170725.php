<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240915170725 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $sql = <<<'SQL'
        ALTER TABLE app_posts ADD COLUMN created_at TIMESTAMP WITH TIME ZONE
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
        CREATE INDEX idx_user_id ON app_posts (user_id)
SQL;
        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $sql = <<<'SQL'
        ALTER TABLE app_posts DROP COLUMN created_at
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
        DROP INDEX idx_user_id
SQL;
        $this->addSql($sql);
    }
}
