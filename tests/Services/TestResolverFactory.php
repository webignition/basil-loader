<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilParser\Resolver\Test\TestResolver;

class TestResolverFactory
{
    public static function create(): TestResolver
    {
        return new TestResolver(
            TestConfigurationResolverFactory::create(),
            StepResolverFactory::create()
        );
    }
}
