<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilParser\Resolver\ActionResolver;

class ActionResolverFactory
{
    public static function create(): ActionResolver
    {
        return new ActionResolver(
            PageModelElementIdentifierResolverFactory::create()
        );
    }
}
