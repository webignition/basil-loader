<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilParser\Factory\ValueFactory;

class ValueFactoryFactory
{
    public static function create(): ValueFactory
    {
        return new ValueFactory();
    }
}
