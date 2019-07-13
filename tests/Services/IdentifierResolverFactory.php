<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilParser\Resolver\IdentifierResolver;

class IdentifierResolverFactory
{
    public static function create(): IdentifierResolver
    {
        return new IdentifierResolver();
    }
}
