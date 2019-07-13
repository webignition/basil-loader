<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilDataStructure\PathResolver;
use webignition\BasilParser\Loader\TestLoader;

class TestLoaderFactory
{
    public static function create(): TestLoader
    {
        return new TestLoader(
            YamlLoaderFactory::create(),
            TestBuilderFactory::create(),
            PathResolver::create(),
            StepProviderFactoryFactory::create(),
            PageProviderFactoryFactory::create(),
            DataSetProviderFactoryFactory::create()
        );
    }
}
