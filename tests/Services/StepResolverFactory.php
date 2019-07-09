<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilParser\Resolver\StepResolver;

class StepResolverFactory
{
    public static function create(): StepResolver
    {
        return new StepResolver(
            ActionResolverFactory::create(),
            AssertionResolverFactory::create(),
            IdentifierResolverFactory::create()
        );
    }
}
