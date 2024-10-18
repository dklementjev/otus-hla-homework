<?php

namespace App\Command\Fixture;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'dev')]
#[AsCommand(name: 'fixture:generate-test-data')]
final class GenerateTestData extends Command
{
    protected const TABLE_NAME = 'test_data';

    public function __construct(
        protected readonly Connection $dbConnection,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('Generate some test data in DB');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $totalRecordsWritten = 0;

        $this->wipeData();
        while (true) {
            [$isDone, $recordsWritten] = $this->writeData(25);
            $totalRecordsWritten += $recordsWritten;
            $output->writeln("Total records written so far: <comment>{$totalRecordsWritten}</comment>");
            if ($isDone) {
                break;
            }
            usleep(1000);
        }

        return 0;
    }

    protected function wipeData(): void
    {
        $sql = 'DELETE FROM '.self::TABLE_NAME;

        $this->dbConnection->executeQuery($sql);
    }

    /**
     * @return array{0: bool, 1: int}
     */
    protected function writeData(int $rowCount): array
    {
        $res = 0;
        $isDone = false;

        $sql = 'INSERT INTO '.self::TABLE_NAME.' (value) VALUES (?)';
        for ($i = 0; $i < $rowCount; ++$i) {
            try {
                $sth = $this->dbConnection->executeQuery($sql, [random_int(0, 1e9)]);
            } catch (\Exception $e) {
                $sth = null;
                $isDone = true;
            }

            if ($sth?->rowCount() > 0) {
                ++$res;
            }
            if ($isDone) {
                break;
            }
        }

        return [
            $isDone,
            $res,
        ];
    }
}
