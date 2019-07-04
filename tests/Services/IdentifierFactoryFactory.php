<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilParser\Factory\IdentifierFactory;

class IdentifierFactoryFactory
{
    public static function create(): IdentifierFactory
    {
        return new IdentifierFactory(
            ValueFactoryFactory::create()
        );
    }
}
