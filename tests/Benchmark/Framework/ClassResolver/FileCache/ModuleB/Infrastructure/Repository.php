<?php

declare(strict_types=1);

namespace GacelaTest\Benchmark\Framework\ClassResolver\FileCache\ModuleB\Infrastructure;

final class Repository
{
    public function getAll(): array
    {
        return ['b'];
    }
}
