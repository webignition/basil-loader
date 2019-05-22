<?php

namespace webignition\BasilParser\Model\Step;

use webignition\BasilParser\Model\Action\ActionInterface;
use webignition\BasilParser\Model\Assertion\AssertionInterface;
use webignition\BasilParser\Model\DataSet\DataSetInterface;

class Step implements StepInterface
{
    /**
     * @var ActionInterface[]
     */
    private $actions = [];

    /**
     * @var AssertionInterface[]
     */
    private $assertions = [];

    /**
     * @var DataSetInterface[]
     */
    private $dataSets = [];

    /**
     * @var string[]
     */
    private $elementReferences = [];

    public function __construct(array $actions, array $assertions, array $dataSets, array $elementReferences)
    {
        foreach ($actions as $action) {
            if ($action instanceof ActionInterface) {
                $this->actions[] = $action;
            }
        }

        foreach ($assertions as $assertion) {
            if ($assertion instanceof AssertionInterface) {
                $this->assertions[] = $assertion;
            }
        }

        foreach ($dataSets as $name => $dataSet) {
            if ($dataSet instanceof DataSetInterface) {
                $this->dataSets[$name] = $dataSet;
            }
        }

        foreach ($elementReferences as $elementName => $elementReference) {
            if (is_string($elementReference)) {
                $this->elementReferences[$elementName] = $elementReference;
            }
        }
    }

    /**
     * @return ActionInterface[]
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * @return AssertionInterface[]
     */
    public function getAssertions(): array
    {
        return $this->assertions;
    }

    /**
     * @return DataSetInterface[]
     */
    public function getDataSets(): array
    {
        return $this->dataSets;
    }

    /**
     * @return string[]
     */
    public function getElementReferences(): array
    {
        return $this->elementReferences;
    }
}
