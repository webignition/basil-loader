<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilParser\Factory\AssertionFactory;
use webignition\BasilParser\Factory\IdentifierFactory;
use webignition\BasilParser\Factory\ValueFactory;

class AssertionFactoryFactory
{
    public static function create(): AssertionFactory
    {
        return new AssertionFactory(
            IdentifierFactoryFactory::create(),
            ValueFactoryFactory::create(),
            IdentifierStringExtractorFactory::create()
        );
    }
}
