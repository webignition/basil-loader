<?php

namespace webignition\BasilParser\Provider\Step;

use webignition\BasilModel\Step\StepInterface;
use webignition\BasilModelFactory\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievableStepException;
use webignition\BasilParser\Exception\UnknownStepException;

interface StepProviderInterface
{
    /**
     * @param string $importName
     *
     * @return StepInterface
     *
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievableStepException
     * @throws UnknownStepException
     */
    public function findStep(string $importName): StepInterface;
}
