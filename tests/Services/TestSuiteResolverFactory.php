<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilParser\Resolver\TestSuiteResolver;

class TestSuiteResolverFactory
{
    public static function create(): TestSuiteResolver
    {
        return new TestSuiteResolver(
            TestResolverFactory::create()
        );
    }
}
