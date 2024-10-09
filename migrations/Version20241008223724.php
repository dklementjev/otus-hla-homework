<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Utils\Migration\ForeignMigration;
use App\Utils\Migration\UseConnection;
use Doctrine\DBAL\Schema\Schema;

#[UseConnection('dialog')]
final class Version20241008223724 extends ForeignMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->executeSql('ALTER TABLE app_dialog_messages DROP CONSTRAINT app_dialog_messages_pkey;');

        $this->executeSql('ALTER TABLE app_dialog_messages ADD PRIMARY KEY (dialog_id, id);');

        $this->executeSql('ALTER TABLE app_dialog_messages DROP CONSTRAINT app_dialog_messages_uuid_key;');

        $this->executeSql('ALTER TABLE app_dialog_messages ADD CONSTRAINT app_dialog_messages_uuid_key UNIQUE (dialog_id, "uuid")');
    }

    public function down(Schema $schema): void
    {
        $this->executeSql('ALTER TABLE app_dialog_messages DROP CONSTRAINT app_dialog_messages_uuid_key;');

        $this->executeSql('ALTER TABLE app_dialog_messages ADD CONSTRAINT app_dialog_messages_uuid_key UNIQUE ("uuid")');

        $this->executeSql('ALTER TABLE app_dialog_messages DROP CONSTRAINT app_dialog_messages_pkey;');

        $this->executeSql('ALTER TABLE app_dialog_messages ADD PRIMARY KEY (id);');
    }
}
