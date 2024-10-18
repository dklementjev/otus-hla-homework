<?php

namespace App\Repository;

use App\Model\ModelInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedLocator;

class RepositoryMapper implements RepositoryMapperInterface
{
    /** @var array<class-string<ModelInterface>, string> */
    protected array $map;

    public function __construct(
        #[TaggedLocator('app.mapped-repository')]
        protected readonly ContainerInterface $repositoryContainer
    ) {
        $this->map = [];
    }

    /**
     * @param string $serviceId
     * @param class-string<ModelInterface> $modelClass
     */
    public function add(string $serviceId, string $modelClass)
    {
        if (!is_a($modelClass, ModelInterface::class, true)) {
            throw new \LogicException($modelClass.' is not an instance of '.ModelInterface::class);
        }

        $this->map[$modelClass] = $serviceId;
    }

    /**
     * @param class-string<ModelInterface> $modelClass
     */
    public function getRepository(string $modelClass): BaseRepository
    {
        $serviceId = $this->map[$modelClass] ?? null;
        if (empty($serviceId)) {
            throw new \LogicException('No mapping for '.$modelClass);
        }

        return $this->repositoryContainer->get($serviceId);
    }
}
