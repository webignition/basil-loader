<?php

namespace webignition\BasilParser\Provider\Step;

use webignition\BasilParser\Exception\NonRetrievableStepException;
use webignition\BasilParser\Exception\UnknownStepException;
use webignition\BasilParser\Model\Step\StepInterface;

interface StepProviderInterface
{
    /**
     * @param string $importName
     *
     * @return StepInterface
     *
     * @throws UnknownStepException
     * @throws NonRetrievableStepException
     */
    public function findStep(string $importName): StepInterface;
}
