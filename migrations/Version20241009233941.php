<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Utils\Migration\ForeignMigration;
use App\Utils\Migration\UseConnection;
use Doctrine\DBAL\Schema\Schema;

#[UseConnection('dialog')]
final class Version20241009233941 extends ForeignMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->executeSql('ALTER TABLE app_dialogs ADD COLUMN is_groupchat BOOLEAN NOT NULL DEFAULT FALSE');
    }

    public function down(Schema $schema): void
    {
        $this->executeSql('ALTER TABLE app_dialogs DROP COLUMN is_groupchat');
    }
}
