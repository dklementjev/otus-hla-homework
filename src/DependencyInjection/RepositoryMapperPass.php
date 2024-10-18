<?php

namespace App\DependencyInjection;

use App\Repository\ModelClass;
use App\Repository\RepositoryMapper;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RepositoryMapperPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(RepositoryMapper::class)) {
            return;
        }

        $tag = 'app.mapped-repository';
        $mapper = $container->findDefinition(RepositoryMapper::class);
        $repositories = $container->findTaggedServiceIds($tag);

        foreach ($repositories as $id => $tags) {
            $repositoryClass = $container->getDefinition($id)->getClass();
            $rc = new \ReflectionClass($repositoryClass);
            $attributes = $rc->getAttributes(ModelClass::class);
            if (empty($attributes)) {
                throw new \LogicException('Missing '.ModelClass::class.' attribute on '.$rc->name);
            }
            /** @var ModelClass $modelClassAttribute */
            $modelClassAttribute = $attributes[0]->newInstance();

            $mapper->addMethodCall('add', [$id, $modelClassAttribute->name]);
        }
    }
}
