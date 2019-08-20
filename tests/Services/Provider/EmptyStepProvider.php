<?php

namespace webignition\BasilParser\Tests\Services\Provider;

use webignition\BasilModel\Step\StepInterface;
use webignition\BasilModelProvider\Exception\UnknownStepException;
use webignition\BasilModelProvider\Step\StepProviderInterface;

class EmptyStepProvider implements StepProviderInterface
{
    /**
     * @param string $importName
     *
     * @return StepInterface
     *
     * @throws UnknownStepException
     */
    public function findStep(string $importName): StepInterface
    {
        throw new UnknownStepException($importName);
    }
}
