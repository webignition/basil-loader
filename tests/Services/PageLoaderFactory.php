<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilModelFactory\PageFactory;
use webignition\BasilParser\Loader\PageLoader;

class PageLoaderFactory
{
    public static function create(): PageLoader
    {
        return new PageLoader(
            YamlLoaderFactory::create(),
            PageFactory::create()
        );
    }
}
