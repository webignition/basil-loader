<?php

namespace webignition\BasilParser\Provider\Step;

use webignition\BasilModel\Step\StepInterface;
use webignition\BasilParser\Exception\UnknownStepException;
use webignition\BasilParser\Provider\DataSet\DataSetProviderInterface;
use webignition\BasilParser\Provider\Page\PageProviderInterface;

class PopulatedStepProvider implements StepProviderInterface
{
    private $steps = [];

    public function __construct(array $steps)
    {
        foreach ($steps as $importName => $step) {
            if ($step instanceof StepInterface) {
                $this->steps[$importName] = $step;
            }
        }
    }

    /**
     * @param string $importName
     * @param StepProviderInterface $stepProvider
     * @param DataSetProviderInterface $dataSetProvider
     * @param PageProviderInterface $pageProvider
     *
     * @return StepInterface
     *
     * @throws UnknownStepException
     */
    public function findStep(
        string $importName,
        StepProviderInterface $stepProvider,
        DataSetProviderInterface $dataSetProvider,
        PageProviderInterface $pageProvider
    ): StepInterface {
        $step = $this->steps[$importName] ?? null;

        if (null === $step) {
            throw new UnknownStepException($importName);
        }

        return $step;
    }
}
