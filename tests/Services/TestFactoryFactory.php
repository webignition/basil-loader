<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilParser\Factory\Test\ConfigurationFactory;
use webignition\BasilParser\Factory\Test\TestFactory;

class TestFactoryFactory
{
    public static function create(): TestFactory
    {
        return new TestFactory(
            new ConfigurationFactory(),
            StepBuilderFactory::create(),
            StepProviderFactoryFactory::create(),
            PageProviderFactoryFactory::create(),
            DataSetProviderFactoryFactory::create()
        );
    }
}
