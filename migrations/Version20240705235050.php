<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240705235050 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $sql = <<<'SQL'
CREATE TABLE app_access_tokens (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES app_users(id),
    token VARCHAR(64) NOT NULL UNIQUE
)
SQL;
        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $sql = 'DROP TABLE app_access_tokens';
        $this->addSql($sql);
    }
}
