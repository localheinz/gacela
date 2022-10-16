<?php

declare(strict_types=1);

namespace GacelaTest\Benchmark\Framework\ClassResolver\FileCache\ModuleB;

use Gacela\Framework\AbstractFacade;
use Gacela\Framework\DocBlockResolverAwareTrait;
use GacelaTest\Benchmark\Framework\ClassResolver\FileCache\ModuleB\Infrastructure\EntityManager;
use GacelaTest\Benchmark\Framework\ClassResolver\FileCache\ModuleB\Infrastructure\Repository;

/**
 * @method FactoryB getFactory()
 * @method Repository getRepository()
 * @method EntityManager getEntityManager()
 */
final class Facade extends AbstractFacade
{
    use DocBlockResolverAwareTrait;

    public function loadGacelaCacheFile(): array
    {
        return [
            $this->getFactory()->getArrayConfigAndProvidedDependency(),
            $this->getRepository()->getAll(),
            $this->getEntityManager()->updateEntity(),
        ];
    }
}
