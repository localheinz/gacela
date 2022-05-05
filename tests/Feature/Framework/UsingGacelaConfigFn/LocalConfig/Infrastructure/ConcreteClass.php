<?php

declare(strict_types=1);

namespace GacelaTest\Feature\Framework\UsingGacelaConfigFn\LocalConfig\Infrastructure;

use GacelaTest\Feature\Framework\UsingGacelaConfigFn\LocalConfig\Domain\AbstractClass;

final class ConcreteClass extends AbstractClass
{
    private bool $bool;
    private string $string;
    private int $int;
    private float $float;
    private array $array;

    public function __construct(bool $bool, string $string, int $int, float $float, array $array)
    {
        $this->bool = $bool;
        $this->string = $string;
        $this->int = $int;
        $this->float = $float;
        $this->array = $array;
    }

    public function getTypes(): array
    {
        return [
            'bool' => $this->bool,
            'string' => $this->string,
            'int' => $this->int,
            'float' => $this->float,
            'array' => $this->array,
        ];
    }
}
