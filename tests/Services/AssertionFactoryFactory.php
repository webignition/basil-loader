<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilParser\Factory\AssertionFactory;
use webignition\BasilParser\Factory\IdentifierFactory;
use webignition\BasilParser\Factory\ValueFactory;
use webignition\BasilParser\IdentifierStringExtractor\IdentifierStringExtractor;

class AssertionFactoryFactory
{
    public static function create(): AssertionFactory
    {
        $identifierFactory = new IdentifierFactory();
        $valueFactory = new ValueFactory();
        $identifierStringExtractor = new IdentifierStringExtractor();

        return new AssertionFactory($identifierFactory, $valueFactory, $identifierStringExtractor);
    }
}
