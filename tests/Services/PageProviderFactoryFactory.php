<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilParser\Loader\PageLoader;
use webignition\BasilParser\Provider\Page\Factory as PageProviderFactory;

class PageProviderFactoryFactory
{
    public static function create(): PageProviderFactory
    {
        return new PageProviderFactory(
            PageLoader::createLoader()
        );
    }
}
