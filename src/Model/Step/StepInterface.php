<?php

namespace webignition\BasilParser\Model\Step;

use webignition\BasilParser\Model\Action\ActionInterface;
use webignition\BasilParser\Model\Assertion\AssertionInterface;
use webignition\BasilParser\Model\DataSet\DataSetInterface;

interface StepInterface
{
    /**
     * @return ActionInterface[]
     */
    public function getActions(): array;

    /**
     * @return AssertionInterface[]
     */
    public function getAssertions() :array;

    /**
     * @return DataSetInterface[]
     */
    public function getDataSets(): array;

    /**
     * @return string[]
     */
    public function getElementReferences(): array;

    /**
     * @param DataSetInterface[] $dataSets
     *
     * @return StepInterface
     */
    public function withDataSets(array $dataSets): StepInterface;

    /**
     * @param string[] $elementReferences
     *
     * @return StepInterface
     */
    public function withElementReferences(array $elementReferences): StepInterface;
}
