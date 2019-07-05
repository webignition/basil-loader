<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilParser\Factory\StepFactory;

class StepFactoryFactory
{
    public static function create(): StepFactory
    {
        return new StepFactory(
            ActionFactoryFactory::create(),
            AssertionFactoryFactory::create(),
            IdentifierFactoryFactory::create()
        );
    }
}
