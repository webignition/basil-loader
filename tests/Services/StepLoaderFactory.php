<?php

namespace webignition\BasilParser\Tests\Services;

use webignition\BasilParser\Loader\StepLoader;

class StepLoaderFactory
{
    public static function create(): StepLoader
    {
        return new StepLoader(
            YamlLoaderFactory::create(),
            StepBuilderFactory::create()
        );
    }
}
