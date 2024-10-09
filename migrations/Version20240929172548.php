<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Utils\Migration\ForeignMigration;
use App\Utils\Migration\UseConnection;
use Doctrine\DBAL\Schema\Schema;

#[UseConnection('dialog')]
final class Version20240929172548 extends ForeignMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $sql = <<< 'SQL'
        CREATE TABLE app_dialog_users (
            id BIGINT PRIMARY KEY,
            nickname VARCHAR(128)
        )
SQL;
        $this->executeSql($sql);

        $sql = <<<'SQL'
        CREATE TABLE app_dialogs (
            id BIGSERIAL PRIMARY KEY, 
            "uuid" UUID UNIQUE, 
            created_at TIMESTAMP WITH TIME ZONE
        )
SQL;
        $this->executeSql($sql);

        $sql = <<<'SQL'
        CREATE TABLE app_dialog_participants (
            dialog_id INT REFERENCES app_dialogs(id) ON DELETE CASCADE, 
            user_id INT REFERENCES app_dialog_users(id) ON DELETE CASCADE,
            PRIMARY KEY (dialog_id, user_id)
        )
SQL;
        $this->executeSql($sql);

        $sql = <<<'SQL'
        CREATE TABLE app_dialog_messages (
            id BIGSERIAL PRIMARY KEY,
            "uuid" UUID UNIQUE,
            user_id INT REFERENCES app_dialog_users(id),
            dialog_id INT REFERENCES app_dialogs(id), 
            message TEXT NOT NULL, 
            created_at TIMESTAMP WITH TIME ZONE
        )
SQL;
        $this->executeSql($sql);

        $sql = <<<'SQL'
        CREATE INDEX idx_thread_messages ON app_dialog_messages(dialog_id, created_at DESC)
SQL;
        $this->executeSql($sql);

    }

    public function down(Schema $schema): void
    {
        $this->executeSql('DROP INDEX idx_thread_messages');

        $this->executeSql('DROP TABLE app_dialog_messages');
        $this->executeSql('DROP TABLE app_dialog_participants');
        $this->executeSql('DROP TABLE app_dialogs');
        $this->executeSql('DROP TABLE app_dialog_users');
    }
}
