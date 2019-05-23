<?php

namespace webignition\BasilParser\Model\Test;

use webignition\BasilParser\Model\Step\StepInterface;

class Test implements TestInterface
{
    private $configuration;
    private $steps;

    public function __construct(ConfigurationInterface $configuration, array $steps)
    {
        $this->configuration = $configuration;

        foreach ($steps as $stepName => $step) {
            if ($step instanceof StepInterface) {
                $this->steps[$stepName] = $step;
            }
        }
    }

    public function getConfiguration(): ConfigurationInterface
    {
        return $this->configuration;
    }

    /**
     * @return StepInterface[]
     */
    public function getSteps(): array
    {
        return $this->steps;
    }
}
