<?php

namespace webignition\BasilParser\Provider\Step;

use webignition\BasilParser\Exception\UnknownStepException;
use webignition\BasilParser\Model\Step\StepInterface;

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
