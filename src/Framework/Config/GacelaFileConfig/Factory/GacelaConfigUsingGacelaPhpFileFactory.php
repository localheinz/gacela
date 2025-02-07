<?php

declare(strict_types=1);

namespace Gacela\Framework\Config\GacelaFileConfig\Factory;

use Gacela\Framework\Bootstrap\GacelaConfig;
use Gacela\Framework\Bootstrap\SetupGacela;
use Gacela\Framework\Bootstrap\SetupGacelaInterface;
use Gacela\Framework\Config\FileIoInterface;
use Gacela\Framework\Config\GacelaConfigBuilder\ConfigBuilder;
use Gacela\Framework\Config\GacelaConfigBuilder\MappingInterfacesBuilder;
use Gacela\Framework\Config\GacelaConfigBuilder\SuffixTypesBuilder;
use Gacela\Framework\Config\GacelaConfigFileFactoryInterface;
use Gacela\Framework\Config\GacelaFileConfig\GacelaConfigFile;
use Gacela\Framework\Config\GacelaFileConfig\GacelaConfigFileInterface;
use RuntimeException;

use function is_callable;

final class GacelaConfigUsingGacelaPhpFileFactory implements GacelaConfigFileFactoryInterface
{
    private string $gacelaPhpPath;

    private SetupGacelaInterface $setup;

    private FileIoInterface $fileIo;

    public function __construct(
        string $gacelaPhpPath,
        SetupGacelaInterface $setup,
        FileIoInterface $fileIo
    ) {
        $this->gacelaPhpPath = $gacelaPhpPath;
        $this->setup = $setup;
        $this->fileIo = $fileIo;
    }

    public function createGacelaFileConfig(): GacelaConfigFileInterface
    {
        $gacelaConfig = $this->createGacelaConfig();
        $setupGacela = SetupGacela::fromGacelaConfig($gacelaConfig);

        $configBuilder = $this->createConfigBuilder($setupGacela);
        $mappingInterfacesBuilder = $this->createMappingInterfacesBuilder($setupGacela);
        $suffixTypesBuilder = $this->createSuffixTypesBuilder($setupGacela);

        return (new GacelaConfigFile())
            ->setConfigItems($configBuilder->build())
            ->setMappingInterfaces($mappingInterfacesBuilder->build())
            ->setSuffixTypes($suffixTypesBuilder->build());
    }

    private function createGacelaConfig(): GacelaConfig
    {
        $gacelaConfig = new GacelaConfig($this->setup->externalServices());

        /** @var callable(GacelaConfig):void|null $configFn */
        $configFn = $this->fileIo->include($this->gacelaPhpPath);

        if (!is_callable($configFn)) {
            throw new RuntimeException('`gacela.php` file should return a `callable(GacelaConfig)`');
        }

        $configFn($gacelaConfig);

        return $gacelaConfig;
    }

    private function createConfigBuilder(SetupGacelaInterface $setupGacela): ConfigBuilder
    {
        return $setupGacela->buildConfig(new ConfigBuilder());
    }

    private function createMappingInterfacesBuilder(SetupGacelaInterface $setupGacela): MappingInterfacesBuilder
    {
        return $setupGacela->buildMappingInterfaces(new MappingInterfacesBuilder(), $this->setup->externalServices());
    }

    private function createSuffixTypesBuilder(SetupGacelaInterface $setupGacela): SuffixTypesBuilder
    {
        return $setupGacela->buildSuffixTypes(new SuffixTypesBuilder());
    }
}
