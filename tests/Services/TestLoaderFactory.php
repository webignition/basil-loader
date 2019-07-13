<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilParser\Loader\TestLoader;

class TestLoaderFactory
{
    public static function create(): TestLoader
    {
        return new TestLoader(
            YamlLoaderFactory::create(),
            TestBuilderFactory::create(),
            PathResolverFactory::create(),
            StepProviderFactoryFactory::create(),
            PageProviderFactoryFactory::create(),
            DataSetProviderFactoryFactory::create()
        );
    }
}
