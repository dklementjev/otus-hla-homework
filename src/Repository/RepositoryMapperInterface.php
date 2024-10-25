<?php

namespace App\Repository;

use App\Model\ModelInterface;

interface RepositoryMapperInterface
{
    /**
     * @param class-string<ModelInterface> $modelClass
     */
    public function getRepository(string $modelClass): BaseRepository;
}
