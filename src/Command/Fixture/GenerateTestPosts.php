<?php

namespace App\Command\Fixture;

use App\Utils;
use Faker;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'dev')]
#[AsCommand(name: 'fixture:generate-test-posts')]
class GenerateTestPosts extends Command
{
    public function __construct(
        protected readonly Faker\Generator $faker,
        protected readonly Utils\Model\User $userUtils,
        protected readonly Utils\Model\Post $postUtils
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Generate test posts')
            ->addOption('post-count-min', null, InputOption::VALUE_REQUIRED, 'Min post count', 10)
            ->addOption('post-count-max', null, InputOption::VALUE_REQUIRED, 'Max post count', 100)
            ->addOption('iterations', null, InputOption::VALUE_REQUIRED, 'Generation iteration count', 1)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $iterationCount = (int) $input->getOption('iterations');
        if ($iterationCount < 1) {
            throw new \InvalidArgumentException('Iteration count is invalid');
        }

        $postCountMin = (int) $input->getOption('post-count-min');
        $postCountMax = (int) $input->getOption('post-count-max');
        if ($postCountMin > $postCountMax) {
            throw new \InvalidArgumentException('Post count options are invalid');
        }

        $output->writeln("Running <comment>{$iterationCount}</comment> iterations");
        $userIds = $this->generateUserIds($iterationCount);

        foreach ($userIds as $userId) {
            $postCount = $this->generatePostCount($postCountMin, $postCountMax);
            $output->writeln("Generating <comment>{$postCount}</comment> posts for user <comment>{$userId}</comment>");
            $actualCount = $this->generatePosts($userId, $postCount);
            $output->writeln("...done, <comment>{$actualCount}</comment> posts generated");
        }

        return 0;
    }

    protected function generatePosts(int $userId, int $postCount): int
    {
        $res = 0;

        for ($i = 0; $i < $postCount; ++$i) {
            if ($this->generatePost($userId)) {
                ++$res;
            }
        }

        return $res;
    }

    protected function generatePost(int $userId): bool
    {
        $text = $this->faker->text(512);
        $post = $this->postUtils->insert(
            $this->postUtils->create($userId, $text)
        );

        return $post !== null;
    }

    /**
     * @return list<int>
     */
    protected function generateUserIds(int $count): array
    {
        return $this->userUtils->pickRandomUserIds($count);
    }

    protected function generatePostCount(int $min, int $max): int
    {
        return rand($min, $max);
    }
}
