<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Utils\Migration\ForeignMigration;
use App\Utils\Migration\UseConnection;
use Doctrine\DBAL\Schema\Schema;

#[UseConnection('dialog')]
final class Version20241008225623 extends ForeignMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $sql = <<<'SQL'
        SELECT create_distributed_table(:table_name, :distribution_column)
SQL;
        $this->getConnection()->executeQuery($sql, ['app_dialog_messages', 'dialog_id']);
    }

    public function down(Schema $schema): void
    {
        $sql = <<<'SQL'
        SELECT undistribute_table(:table_name)
SQL;
        $this->getConnection()->executeQuery($sql, ['app_dialog_messages']);
    }
}
