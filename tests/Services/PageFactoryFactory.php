<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilParser\Factory\PageFactory;

class PageFactoryFactory
{
    public static function create(): PageFactory
    {
        return new PageFactory(
            IdentifierFactoryFactory::create()
        );
    }
}
