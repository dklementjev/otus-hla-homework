<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Utils\Migration\ForeignMigration;
use App\Utils\Migration\UseConnection;
use Doctrine\DBAL\Schema\Schema;

#[UseConnection('dialog')]
final class Version20241013175223 extends ForeignMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->executeSql('ALTER TABLE app_dialog_users ALTER COLUMN nickname DROP NOT NULL');
        $this->executeSql('ALTER TABLE app_dialog_users ADD column "uuid" UUID NOT NULL UNIQUE DEFAULT gen_random_uuid()');
    }

    public function down(Schema $schema): void
    {
        $this->executeSql('ALTER TABLE app_dialog_users DROP COLUMN "uuid"');
        $this->executeSql('ALTER TABLE app_dialog_users ALTER COLUMN nickname SET NOT NULL');
    }
}
