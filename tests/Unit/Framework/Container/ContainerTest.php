<?php

declare(strict_types=1);

namespace GacelaTest\Unit\Framework\Container;

use Gacela\Framework\Container\Container;
use Gacela\Framework\Container\Exception\ContainerException;
use Gacela\Framework\Container\Exception\ContainerKeyNotFoundException;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ContainerTest extends TestCase
{
    private Container $container;

    public function setUp(): void
    {
        $this->container = new Container();
    }

    public function test_get_non_existing_service(): void
    {
        $this->expectException(ContainerKeyNotFoundException::class);
        $this->container->get('unknown-service_name');
    }

    public function test_has_service(): void
    {
        $this->container->set('service_name', 'value');

        self::assertTrue($this->container->has('service_name'));
        self::assertFalse($this->container->has('unknown-service_name'));
    }

    public function test_remove_existing_service(): void
    {
        $this->container->set('service_name', 'value');
        $this->container->remove('service_name');

        $this->expectException(ContainerKeyNotFoundException::class);
        $this->container->get('service_name');
    }

    public function test_resolve_service_as_raw_string(): void
    {
        $this->container->set('service_name', 'value');

        $resolvedService = $this->container->get('service_name');
        self::assertSame('value', $resolvedService);

        $cachedResolvedService = $this->container->get('service_name');
        self::assertSame('value', $cachedResolvedService);
    }

    public function test_resolve_service_as_function(): void
    {
        $this->container->set('service_name', static fn (): string => 'value');

        $resolvedService = $this->container->get('service_name');
        self::assertSame('value', $resolvedService);

        $cachedResolvedService = $this->container->get('service_name');
        self::assertSame('value', $cachedResolvedService);
    }

    public function test_resolve_service_as_callable_class(): void
    {
        $this->container->set(
            'service_name',
            new class() {
                public function __invoke(): string
                {
                    return 'value';
                }
            }
        );

        $resolvedService = $this->container->get('service_name');
        self::assertSame('value', $resolvedService);

        $cachedResolvedService = $this->container->get('service_name');
        self::assertSame('value', $cachedResolvedService);
    }

    public function test_resolve_non_factory_service_with_random(): void
    {
        $this->container->set(
            'service_name',
            new class() {
                public function __invoke(): string
                {
                    return 'value_' . random_int(0, PHP_INT_MAX);
                }
            }
        );

        self::assertSame(
            $this->container->get('service_name'),
            $this->container->get('service_name')
        );
    }

    public function test_resolve_factory_service_with_random(): void
    {
        $this->container->set(
            'service_name',
            $this->container->factory(
                new class() {
                    public function __invoke(): string
                    {
                        return 'value_' . random_int(0, PHP_INT_MAX);
                    }
                }
            )
        );

        self::assertNotSame(
            $this->container->get('service_name'),
            $this->container->get('service_name')
        );
    }

    public function test_resolve_factory_service_not_invokable(): void
    {
        $this->expectExceptionObject(ContainerException::serviceNotInvokable());

        $this->container->set(
            'service_name',
            $this->container->factory(new stdClass())
        );
    }
}
