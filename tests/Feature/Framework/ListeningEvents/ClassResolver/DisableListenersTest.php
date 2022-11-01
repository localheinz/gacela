<?php

declare(strict_types=1);

namespace GacelaTest\Feature\Framework\ListeningEvents\ClassResolver;

use Gacela\Framework\Bootstrap\GacelaConfig;
use Gacela\Framework\EventListener\ClassResolver\ResolvedClassCachedEvent;
use Gacela\Framework\EventListener\ClassResolver\ResolvedClassCreatedEvent;
use Gacela\Framework\EventListener\ClassResolver\ResolvedClassTriedFromParentEvent;
use Gacela\Framework\EventListener\ClassResolver\ResolvedCreatedDefaultClassEvent;
use Gacela\Framework\Gacela;
use GacelaTest\Feature\Framework\ListeningEvents\ClassResolver\Module\Facade;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class DisableListenersTest extends TestCase
{
    public function test_disable_class_resolver_listener(): void
    {
        Gacela::bootstrap(__DIR__, function (GacelaConfig $config): void {
            $config->disableEventListeners();

            $config->registerSpecificListener(ResolvedClassCachedEvent::class, [$this, 'throwExceptionListener']);
            $config->registerSpecificListener(ResolvedClassCreatedEvent::class, [$this, 'throwExceptionListener']);
            $config->registerSpecificListener(ResolvedClassTriedFromParentEvent::class, [$this, 'throwExceptionListener']);
            $config->registerSpecificListener(ResolvedCreatedDefaultClassEvent::class, [$this, 'throwExceptionListener']);
        });

        $facade = new Facade();
        $facade->doString();

        $facade = new Facade();
        $facade->doString();

        $this->expectNotToPerformAssertions();
    }

    public function throwExceptionListener(): void
    {
        throw new RuntimeException('This should never be called');
    }
}
