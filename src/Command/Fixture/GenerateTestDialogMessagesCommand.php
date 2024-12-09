<?php

declare(strict_types=1);

namespace App\Command\Fixture;

use App\Utils\Model\DialogInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'dev')]
#[AsCommand(name: 'fixture:generate-test-dialog-messages', description: 'Hello PhpStorm')]
class GenerateTestDialogMessagesCommand extends Command
{
    public function __construct(
        protected readonly DialogInterface $dialogMessageUtils
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Generate test data for dialog module')
            ->addOption('from', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'From user id (s)')
            ->addOption('to', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'To user id(s)')
            ->addOption('count', null, InputOption::VALUE_REQUIRED, 'Message count', 100)
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Dry run')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $i = 0;
        $count = $input->getOption('count');
        $fromUserIds = $input->getOption('from');
        $toUserIds = $input->getOption('to');
        $isDryRun = $input->getOption('dry-run');

        if (count($fromUserIds) < 1) {
            throw new \InvalidArgumentException('--from must be non-empty');
        }
        if (count($toUserIds) < 1) {
            throw new \InvalidArgumentException('--to must be non-empty');
        }

        $progressBar = new ProgressBar($output, $count);
        $progressBar->start();
        while ($i < $count) {
            $userFrom = (int) $fromUserIds[array_rand($fromUserIds)];
            $userTo = (int) $toUserIds[array_rand($toUserIds)];

            $output->writeln(
                sprintf(
                    'Generating message %u -> %u',
                    $userFrom,
                    $userTo
                )
            );
            if (!$isDryRun) {
                $dialog = $this->dialogMessageUtils->getOrCreatePMForUsers($userFrom, $userTo);
                $this->dialogMessageUtils->createMessage($userFrom, $dialog->getId(), $this->generateMessage());
            }
            ++$i;
            $progressBar->advance();
        }
        $progressBar->finish();

        return Command::SUCCESS;
    }

    private function generateMessage(): string
    {
        return sprintf(
            'Message %s',
            Uuid::uuid7()
        );
    }
}
