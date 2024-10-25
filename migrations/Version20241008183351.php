<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Utils\Migration\ForeignMigration;
use App\Utils\Migration\UseConnection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\Exception\IrreversibleMigration;

#[UseConnection('dialog')]
final class Version20241008183351 extends ForeignMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $referenceTableNames = [
            'app_dialog_users',
            'app_dialogs',
            'app_dialog_participants',
        ];

        foreach ($referenceTableNames as $tableName) {
            $this->createReferenceTable($tableName);
        }
    }

    public function down(Schema $schema): void
    {
        throw new IrreversibleMigration();
    }

    protected function createReferenceTable(string $tableName)
    {
        $this->executeSql(
            'SELECT create_reference_table(:table_name) ',
            ['table_name' => $tableName]
        );
    }
}
