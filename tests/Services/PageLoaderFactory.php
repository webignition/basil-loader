<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilParser\Loader\PageLoader;

class PageLoaderFactory
{
    public static function create(): PageLoader
    {
        return new PageLoader(
            YamlLoaderFactory::create(),
            PageFactoryFactory::create()
        );
    }
}
