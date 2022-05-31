<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Resolver;

use webignition\BasilModels\Model\Test\Configuration;
use webignition\BasilModels\Model\Test\ConfigurationInterface;
use webignition\BasilModels\Provider\Exception\UnknownItemException;
use webignition\BasilModels\Provider\ProviderInterface;

class TestConfigurationResolver
{
    public function __construct(
        private ImportedUrlResolver $importedUrlResolver
    ) {
    }

    public static function createResolver(): TestConfigurationResolver
    {
        return new TestConfigurationResolver(
            ImportedUrlResolver::createResolver()
        );
    }

    /**
     * @throws UnknownItemException
     */
    public function resolve(
        ConfigurationInterface $configuration,
        ProviderInterface $pageProvider
    ): ConfigurationInterface {
        return new Configuration(
            $configuration->getBrowser(),
            $this->importedUrlResolver->resolve($configuration->getUrl(), $pageProvider)
        );
    }
}
