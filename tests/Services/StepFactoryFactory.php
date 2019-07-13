<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilModelFactory\Action\ActionFactory;
use webignition\BasilModelFactory\AssertionFactory;
use webignition\BasilParser\Factory\StepFactory;

class StepFactoryFactory
{
    public static function create(): StepFactory
    {
        return new StepFactory(
            ActionFactory::createFactory(),
            AssertionFactory::createFactory(),
            IdentifierFactoryFactory::create()
        );
    }
}
