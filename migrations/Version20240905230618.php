<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240905230618 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $sql = <<<'SQL'
        CREATE TABLE app_user_friends (
          user_id int NOT NULL REFERENCES app_users(id) ON DELETE CASCADE,
          friend_id int NOT NULL REFERENCES app_users(id) ON DELETE CASCADE,
          PRIMARY KEY(user_id, friend_id)
        )
SQL;
        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $sql = <<<SQL
        DROP TABLE app_user_friends
SQL;
        $this->addSql($sql);
    }
}
