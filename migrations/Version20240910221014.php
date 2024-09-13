<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240910221014 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $sql = <<<'SQL'
        CREATE TABLE app_posts (
            "id" SERIAL PRIMARY KEY,
            "user_id" INT NOT NULL REFERENCES app_users(id) ON DELETE CASCADE,
            "uuid" UUID NOT NULL,
            "text" TEXT,
            UNIQUE("uuid")
        )
SQL;
        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $sql = <<<'SQL'
        DROP TABLE app_posts
SQL;
        $this->addSql($sql);
    }
}
