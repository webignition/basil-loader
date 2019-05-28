<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilParser\Factory\IdentifierFactory;
use webignition\BasilParser\Factory\PageFactory;

class PageFactoryFactory
{
    public static function create(): PageFactory
    {
        $identifierFactory = new IdentifierFactory();

        return new PageFactory($identifierFactory);
    }
}
