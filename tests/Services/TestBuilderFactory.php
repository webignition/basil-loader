<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilModelFactory\Test\TestFactory;
use webignition\BasilParser\Builder\TestBuilder;

class TestBuilderFactory
{
    public static function create(): TestBuilder
    {
        return new TestBuilder(
            TestFactory::createFactory(),
            TestResolverFactory::create()
        );
    }
}
