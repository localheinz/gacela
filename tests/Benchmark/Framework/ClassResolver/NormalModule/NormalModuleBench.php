<?php

declare(strict_types=1);

namespace GacelaTest\Benchmark\Framework\ClassResolver\NormalModule;

/**
 * @BeforeMethods("setUp")
 */
final class NormalModuleBench
{
    private NormalModuleFacade $facade;

    public function setUp(): void
    {
        $this->facade = new NormalModuleFacade();
    }

    public function bench_class_resolving(): void
    {
        $this->facade->getConfigValues();
        $this->facade->getValueFromDependencyProvider();
    }
}
