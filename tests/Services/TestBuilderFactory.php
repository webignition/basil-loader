<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilParser\Builder\TestBuilder;

class TestBuilderFactory
{
    public static function create(): TestBuilder
    {
        return new TestBuilder(
            TestFactoryFactory::create(),
            TestResolverFactory::create()
        );
    }
}
