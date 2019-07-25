<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilParser\Resolver\ActionResolver;
use webignition\BasilParser\Resolver\AssertionResolver;
use webignition\BasilParser\Resolver\IdentifierResolver;
use webignition\BasilParser\Resolver\StepResolver;

class StepResolverFactory
{
    public static function create(): StepResolver
    {
        return new StepResolver(
            ActionResolver::createResolver(),
            AssertionResolver::createResolver(),
            IdentifierResolver::createResolver()
        );
    }
}
