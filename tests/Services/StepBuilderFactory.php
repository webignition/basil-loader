<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilModelFactory\StepFactory;
use webignition\BasilParser\Builder\StepBuilder;

class StepBuilderFactory
{
    public static function create(): StepBuilder
    {
        return new StepBuilder(
            StepFactory::createFactory(),
            StepResolverFactory::create()
        );
    }
}
