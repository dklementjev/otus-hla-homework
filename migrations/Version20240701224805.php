<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240701224805 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $sql = <<<'SQL'
CREATE TABLE app_users (
    id SERIAL PRIMARY KEY, 
    first_name varchar(255) NOT NULL, 
    last_name varchar(255) NOT NULL, 
    birthdate date NOT NULL,
    city varchar(255) NOT NULL,
    pass varchar(255) NOT NULL,
    bio text NOT NULL
)
SQL;

        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $sql = <<<'SQL'
DROP TABLE app_users
SQL;
        $this->addSql($sql);
    }
}
