<?php

declare(strict_types=1);

namespace GacelaTest\Integration\Framework\Config;

use Gacela\Framework\Bootstrap\GacelaConfig;
use Gacela\Framework\Config\Config;
use Gacela\Framework\Gacela;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    public function setUp(): void
    {
        Gacela::bootstrap(__DIR__, static fn (GacelaConfig $config) => $config->setResetCache(true));
    }

    public function test_get_undefined_key(): void
    {
        $this->expectExceptionMessageMatches('/Could not find config key "undefined-key"/');
        Config::getInstance()->get('undefined-key');
    }

    public function test_get_default_value_from_undefined_key(): void
    {
        self::assertSame('default', Config::getInstance()->get('undefined-key', 'default'));
    }

    public function test_null_as_default_value_from_undefined_key(): void
    {
        self::assertNull(Config::getInstance()->get('undefined-key', null));
    }
}
