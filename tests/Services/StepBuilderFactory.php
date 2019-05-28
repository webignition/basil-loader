<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilParser\Builder\StepBuilder;

class StepBuilderFactory
{
    public static function create(): StepBuilder
    {
        return new StepBuilder(
            StepFactoryFactory::create()
        );
    }
}
