<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilParser\Factory\AssertionFactory;
use webignition\BasilParser\Factory\IdentifierFactory;
use webignition\BasilParser\Factory\ValueFactory;

class AssertionFactoryFactory
{
    public static function create(): AssertionFactory
    {
        $identifierFactory = new IdentifierFactory();
        $valueFactory = new ValueFactory();

        return new AssertionFactory(
            $identifierFactory,
            $valueFactory,
            IdentifierStringExtractorFactory::create()
        );
    }
}
