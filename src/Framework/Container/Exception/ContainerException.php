<?php

declare(strict_types=1);

namespace Gacela\Framework\Container\Exception;

use Exception;
use Psr\Container\ContainerExceptionInterface;

final class ContainerException extends Exception implements ContainerExceptionInterface
{
    public static function serviceNotInvokable(): self
    {
        return new self('The passed service is not invokable.');
    }
}
