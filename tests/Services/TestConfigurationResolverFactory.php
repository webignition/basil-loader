<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilParser\Resolver\Test\ConfigurationResolver;

class TestConfigurationResolverFactory
{
    public static function create(): ConfigurationResolver
    {
        return new ConfigurationResolver();
    }
}
