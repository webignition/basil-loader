<?php

namespace webignition\BasilParser\Provider\Step;

use webignition\BasilParser\Loader\StepLoader;

class Factory
{
    private $stepLoader;

    public function __construct(StepLoader $stepLoader)
    {
        $this->stepLoader = $stepLoader;
    }

    public static function createFactory(): Factory
    {
        return new Factory(
            StepLoader::createLoader()
        );
    }

    public function createDeferredStepProvider(array $importPaths)
    {
        return new DeferredStepProvider($this->stepLoader, $importPaths);
    }
}
