<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilParser\Provider\Page\Factory as PageProviderFactory;

class PageProviderFactoryFactory
{
    public static function create(): PageProviderFactory
    {
        return new PageProviderFactory(
            PageLoaderFactory::create()
        );
    }
}
