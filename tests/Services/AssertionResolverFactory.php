<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilParser\Resolver\AssertionResolver;

class AssertionResolverFactory
{
    public static function create(): AssertionResolver
    {
        return new AssertionResolver(
            PageModelElementIdentifierResolverFactory::create()
        );
    }
}
