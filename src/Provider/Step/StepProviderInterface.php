<?php

namespace webignition\BasilParser\Provider\Step;

use webignition\BasilModel\Step\StepInterface;
use webignition\BasilModelProvider\Exception\UnknownStepException;

interface StepProviderInterface
{
    /**
     * @param string $importName
     *
     * @return StepInterface
     *
     * @throws UnknownStepException
     */
    public function findStep(string $importName): StepInterface;
}
