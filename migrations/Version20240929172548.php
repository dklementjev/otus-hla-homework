<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240929172548 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $sql = <<<'SQL'
        CREATE TABLE app_dialogs (
            id BIGSERIAL PRIMARY KEY, 
            "uuid" UUID UNIQUE, 
            created_at TIMESTAMP WITH TIME ZONE
        )
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
        CREATE TABLE app_dialog_participants (
            dialog_id INT REFERENCES app_dialogs(id) ON DELETE CASCADE, 
            user_id INT REFERENCES app_users(id) ON DELETE CASCADE,
            PRIMARY KEY (dialog_id, user_id)
        )
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
        CREATE TABLE app_dialog_messages (
            id BIGSERIAL PRIMARY KEY,
            "uuid" UUID UNIQUE,
            user_id INT REFERENCES app_users(id),
            dialog_id INT REFERENCES app_dialogs(id), 
            message TEXT NOT NULL, 
            created_at TIMESTAMP WITH TIME ZONE
        )
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
        CREATE INDEX idx_thread_messages ON app_dialog_messages(dialog_id, created_at DESC)
SQL;
        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_thread_messages');

        $this->addSql('DROP TABLE app_dialog_messages');
        $this->addSql('DROP TABLE app_dialog_participants');
        $this->addSql('DROP TABLE app_dialogs');
    }
}
