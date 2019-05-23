<?php

namespace webignition\BasilParser\Model\Test;

use webignition\BasilParser\Model\Step\StepInterface;

interface TestInterface
{
    public function getConfiguration(): ConfigurationInterface;

    /**
     * @return StepInterface[]
     */
    public function getSteps(): array;
}
