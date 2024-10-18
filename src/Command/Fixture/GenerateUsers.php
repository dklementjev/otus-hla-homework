<?php

namespace App\Command\Fixture;

use App\Repository\UserRepository;
use App\Utils\Model\User;
use Doctrine\DBAL\Connection;
use Faker;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'dev')]
#[AsCommand(name: 'fixture:generate-users')]
class GenerateUsers extends Command
{
    public function __construct(
        protected readonly User $userUtils,
        protected readonly UserRepository $userRepository,
        protected readonly Faker\Generator $faker,
        protected readonly Connection $dbConnection,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('Generate users up to limit')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Max user count')
            ->addOption('chunk-size', null, InputOption::VALUE_REQUIRED, 'Max chunk size', 100)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $limit = (int) $input->getOption('limit');
        if ($limit < 1) {
            throw new \InvalidArgumentException('Limit must be non-negative');
        }
        $chunkSize = (int) $input->getOption('chunk-size');
        if ($chunkSize < 1) {
            throw new \InvalidArgumentException('Chunk size must be non-negative');
        }
        $this->generateUsers($chunkSize, $limit, $output);

        return 0;
    }

    /**
     * @phpstan-param positive-int $chunkSize
     * @phpstan-param positive-int $limit
     */
    protected function generateUsers(int $chunkSize, int $limit, OutputInterface $output): void
    {
        $userCount = $this->userUtils->count();
        $UTC = new \DateTimeZone('UTC');

        while ($userCount < $limit) {
            if ($output->isVerbose()) {
                $output->writeln("Current user count: <info>{$userCount}</info>");
            }

            $this->dbConnection->transactional(fn () => $this->generateChunk(min($chunkSize, $limit - $userCount), $UTC));

            $newUserCount = $this->userUtils->count();
            if ($userCount === $newUserCount) {
                throw new \UnexpectedValueException('No users added in current iteration');
            }
            $userCount = $newUserCount;
        }
    }

    protected function generateChunk(int $count, \DateTimeZone $tz): void
    {
        for ($i = 0; $i < $count; ++$i) {
            $model = $this->userRepository->create();
            $model->setFirstName($this->faker->firstName())
                ->setLastName($this->faker->lastName())
                ->setBirthdate(new \DateTimeImmutable($this->faker->date(max: '-20 years'), $tz))
                ->setCity($this->faker->city())
                ->setBio('')
                ->setPasswordHash('<invalid>')
            ;
            $this->userRepository->insert($model);
        }
    }
}
