<?php

namespace webignition\BasilParser\Model\Step;

use webignition\BasilParser\Model\Action\ActionInterface;
use webignition\BasilParser\Model\Assertion\AssertionInterface;
use webignition\BasilParser\Model\DataSet\DataSetInterface;
use webignition\BasilParser\Model\Identifier\IdentifierInterface;

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
     * @return IdentifierInterface[]
     */
    public function getElementIdentifiers(): array;

    /**
     * @param DataSetInterface[] $dataSets
     *
     * @return StepInterface
     */
    public function withDataSets(array $dataSets): StepInterface;

    /**
     * @param IdentifierInterface[] $elementIdentifiers
     *
     * @return StepInterface
     */
    public function withElementIdentifiers(array $elementIdentifiers): StepInterface;
}
