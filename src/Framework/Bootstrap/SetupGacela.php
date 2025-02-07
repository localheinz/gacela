<?php

declare(strict_types=1);

namespace Gacela\Framework\Bootstrap;

use Gacela\Framework\ClassResolver\Cache\GacelaFileCache;
use Gacela\Framework\Config\GacelaConfigBuilder\ConfigBuilder;
use Gacela\Framework\Config\GacelaConfigBuilder\MappingInterfacesBuilder;
use Gacela\Framework\Config\GacelaConfigBuilder\SuffixTypesBuilder;
use Gacela\Framework\Event\Dispatcher\ConfigurableEventDispatcher;
use Gacela\Framework\Event\Dispatcher\EventDispatcherInterface;
use Gacela\Framework\Event\Dispatcher\NullEventDispatcher;
use RuntimeException;

use function is_callable;

final class SetupGacela extends AbstractSetupGacela
{
    /** @var callable(ConfigBuilder):void */
    private $configFn;

    /** @var callable(MappingInterfacesBuilder,array<string,mixed>):void */
    private $mappingInterfacesFn;

    /** @var callable(SuffixTypesBuilder):void */
    private $suffixTypesFn;

    /** @var array<string,class-string|object|callable> */
    private array $externalServices = [];

    private ?ConfigBuilder $configBuilder = null;

    private ?SuffixTypesBuilder $suffixTypesBuilder = null;

    private ?MappingInterfacesBuilder $mappingInterfacesBuilder = null;

    private bool $shouldResetInMemoryCache = false;

    private bool $fileCacheEnabled = GacelaFileCache::DEFAULT_ENABLED_VALUE;

    private string $fileCacheDirectory = GacelaFileCache::DEFAULT_DIRECTORY_VALUE;

    /** @var list<string> */
    private array $projectNamespaces = [];

    /** @var array<string,mixed> */
    private array $configKeyValues = [];

    private bool $areEventListenersEnabled = false;

    /** @var list<callable> */
    private array $genericListeners = [];

    /** @var array<class-string,list<callable>> */
    private array $specificListeners = [];

    private ?EventDispatcherInterface $eventDispatcher = null;

    public function __construct()
    {
        $this->configFn = static function (): void {
        };
        $this->mappingInterfacesFn = static function (): void {
        };
        $this->suffixTypesFn = static function (): void {
        };
    }

    public static function fromFile(string $gacelaFilePath): self
    {
        if (!is_file($gacelaFilePath)) {
            throw new RuntimeException("Invalid file path: '{$gacelaFilePath}'");
        }

        /** @var callable(GacelaConfig):void|null $setupGacelaFileFn */
        $setupGacelaFileFn = include $gacelaFilePath;
        if (!is_callable($setupGacelaFileFn)) {
            return new self();
        }

        return self::fromCallable($setupGacelaFileFn);
    }

    /**
     * @param callable(GacelaConfig):void $setupGacelaFileFn
     */
    public static function fromCallable(callable $setupGacelaFileFn): self
    {
        $gacelaConfig = new GacelaConfig();
        $setupGacelaFileFn($gacelaConfig);

        return self::fromGacelaConfig($gacelaConfig);
    }

    public static function fromGacelaConfig(GacelaConfig $gacelaConfig): self
    {
        $build = $gacelaConfig->build();

        return (new self())
            ->setConfigBuilder($build['config-builder'])
            ->setSuffixTypesBuilder($build['suffix-types-builder'])
            ->setMappingInterfacesBuilder($build['mapping-interfaces-builder'])
            ->setExternalServices($build['external-services'])
            ->setShouldResetInMemoryCache($build['should-reset-in-memory-cache'])
            ->setFileCacheEnabled($build['file-cache-enabled'])
            ->setFileCacheDirectory($build['file-cache-directory'])
            ->setProjectNamespaces($build['project-namespaces'])
            ->setConfigKeyValues($build['config-key-values'])
            ->setAreEventListenersEnabled($build['are-event-listeners-enabled'])
            ->setGenericListeners($build['generic-listeners'])
            ->setSpecificListeners($build['specific-listeners']);
    }

    public function setMappingInterfacesBuilder(MappingInterfacesBuilder $builder): self
    {
        $this->mappingInterfacesBuilder = $builder;

        return $this;
    }

    public function setSuffixTypesBuilder(SuffixTypesBuilder $builder): self
    {
        $this->suffixTypesBuilder = $builder;

        return $this;
    }

    public function setConfigBuilder(ConfigBuilder $builder): self
    {
        $this->configBuilder = $builder;

        return $this;
    }

    /**
     * @param callable(ConfigBuilder):void $callable
     */
    public function setConfigFn(callable $callable): self
    {
        $this->configFn = $callable;

        return $this;
    }

    public function buildConfig(ConfigBuilder $builder): ConfigBuilder
    {
        if ($this->configBuilder) {
            $builder = $this->configBuilder;
        }

        ($this->configFn)($builder);

        return $builder;
    }

    /**
     * @param callable(MappingInterfacesBuilder,array<string,mixed>):void $callable
     */
    public function setMappingInterfacesFn(callable $callable): self
    {
        $this->mappingInterfacesFn = $callable;

        return $this;
    }

    /**
     * Define the mapping between interfaces and concretions, so Gacela services will auto-resolve them automatically.
     *
     * @param array<string,class-string|object|callable> $externalServices
     */
    public function buildMappingInterfaces(
        MappingInterfacesBuilder $builder,
        array $externalServices
    ): MappingInterfacesBuilder {
        if ($this->mappingInterfacesBuilder) {
            $builder = $this->mappingInterfacesBuilder;
        }

        ($this->mappingInterfacesFn)(
            $builder,
            array_merge($this->externalServices, $externalServices)
        );

        return $builder;
    }

    /**
     * @param callable(SuffixTypesBuilder):void $callable
     */
    public function setSuffixTypesFn(callable $callable): self
    {
        $this->suffixTypesFn = $callable;

        return $this;
    }

    /**
     * Allow overriding gacela resolvable types.
     */
    public function buildSuffixTypes(SuffixTypesBuilder $builder): SuffixTypesBuilder
    {
        if ($this->suffixTypesBuilder) {
            $builder = $this->suffixTypesBuilder;
        }

        ($this->suffixTypesFn)($builder);

        return $builder;
    }

    /**
     * @param array<string,class-string|object|callable> $array
     */
    public function setExternalServices(array $array): self
    {
        $this->externalServices = $array;

        return $this;
    }

    /**
     * @return array<string,class-string|object|callable>
     */
    public function externalServices(): array
    {
        return $this->externalServices;
    }

    public function setShouldResetInMemoryCache(bool $flag): self
    {
        $this->shouldResetInMemoryCache = $flag;

        return $this;
    }

    public function shouldResetInMemoryCache(): bool
    {
        return $this->shouldResetInMemoryCache;
    }

    public function setFileCacheEnabled(bool $flag): self
    {
        $this->fileCacheEnabled = $flag;

        return $this;
    }

    public function isFileCacheEnabled(): bool
    {
        return $this->fileCacheEnabled;
    }

    public function getFileCacheDirectory(): string
    {
        return $this->fileCacheDirectory;
    }

    public function setFileCacheDirectory(string $dir): self
    {
        $this->fileCacheDirectory = $dir;

        return $this;
    }

    /**
     * @param list<string> $list
     */
    public function setProjectNamespaces(array $list): self
    {
        $this->projectNamespaces = $list;

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getProjectNamespaces(): array
    {
        return $this->projectNamespaces;
    }

    /**
     * @return array<string,mixed>
     */
    public function getConfigKeyValues(): array
    {
        return $this->configKeyValues;
    }

    public function getEventDispatcher(): EventDispatcherInterface
    {
        if ($this->eventDispatcher !== null) {
            return $this->eventDispatcher;
        }

        if ($this->areEventListenersEnabled) {
            $this->eventDispatcher = new ConfigurableEventDispatcher();
            $this->eventDispatcher->registerGenericListeners($this->genericListeners);

            foreach ($this->specificListeners as $event => $listeners) {
                foreach ($listeners as $callable) {
                    $this->eventDispatcher->registerSpecificListener($event, $callable);
                }
            }
        } else {
            $this->eventDispatcher = new NullEventDispatcher();
        }

        return $this->eventDispatcher;
    }

    public function setAreEventListenersEnabled(bool $flag): self
    {
        $this->areEventListenersEnabled = $flag;

        return $this;
    }

    /**
     * @param array<string,mixed> $configKeyValues
     */
    private function setConfigKeyValues(array $configKeyValues): self
    {
        $this->configKeyValues = $configKeyValues;

        return $this;
    }

    /**
     * @param list<callable> $listeners
     */
    private function setGenericListeners(array $listeners): self
    {
        $this->genericListeners = $listeners;

        return $this;
    }

    /**
     * @param array<class-string,list<callable>> $listeners
     */
    private function setSpecificListeners(array $listeners): self
    {
        $this->specificListeners = $listeners;

        return $this;
    }
}
