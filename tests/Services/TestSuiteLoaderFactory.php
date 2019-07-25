<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilDataStructure\PathResolver;
use webignition\BasilParser\Loader\TestLoader;
use webignition\BasilParser\Loader\TestSuiteLoader;
use webignition\BasilParser\Loader\YamlLoader;

class TestSuiteLoaderFactory
{
    public static function create(): TestSuiteLoader
    {
        return new TestSuiteLoader(
            YamlLoader::createLoader(),
            TestLoader::createLoader(),
            PathResolver::create()
        );
    }
}
