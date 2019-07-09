<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilParser\Resolver\IdentifierContainerIdentifierResolver;

class IdentifierContainerIdentifierResolverFactory
{
    public static function create(): IdentifierContainerIdentifierResolver
    {
        return new IdentifierContainerIdentifierResolver(
            IdentifierResolverFactory::create()
        );
    }
}
