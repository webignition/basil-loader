<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilParser\Resolver\PageModelElementIdentifierResolver;

class PageModelElementIdentifierResolverFactory
{
    public static function create(): PageModelElementIdentifierResolver
    {
        return new PageModelElementIdentifierResolver();
    }
}
