<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilParser\Provider\Step\Factory as StepProviderFactory;

class StepProviderFactoryFactory
{
    public static function create(): StepProviderFactory
    {
        return new StepProviderFactory(
            StepLoaderFactory::create()
        );
    }
}
