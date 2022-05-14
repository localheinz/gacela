<?php

declare(strict_types=1);

namespace Gacela\Framework;

use Gacela\Framework\Bootstrap\GacelaConfig;
use Gacela\Framework\Bootstrap\SetupGacela;
use Gacela\Framework\Bootstrap\SetupGacelaInterface;

use function is_callable;

final class Gacela
{
    /**
     * Define the entry point of Gacela.
     *
     * @param null|SetupGacelaInterface|callable(GacelaConfig):void $configFn SetupGacelaInterface is deprecated
     */
    public static function bootstrap(string $appRootDir, $configFn = null): void
    {
        if ($configFn instanceof SetupGacelaInterface) {
            trigger_deprecation(
                'gacela-project/gacela',
                '0.18',
                '`SetupGacelaInterface` is deprecated and it will be removed in a future version. Use `callable(GacelaConfig)` instead.'
            );
        }

        $setup = self::normalizeSetup($configFn);

        Config::getInstance()
            ->setAppRootDir($appRootDir)
            ->setSetup($setup)
            ->init();
    }

    /**
     * @param null|SetupGacelaInterface|callable(GacelaConfig):void $configFn
     */
    private static function normalizeSetup($configFn): SetupGacelaInterface
    {
        if ($configFn === null) {
            return new SetupGacela();
        }

        if (is_callable($configFn)) {
            return SetupGacela::fromCallable($configFn);
        }

        return $configFn;
    }
}
