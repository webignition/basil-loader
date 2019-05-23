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

    public function __construct(array $actions, array $assertions)
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

    /**
     * @param DataSetInterface[] $dataSets
     *
     * @return StepInterface
     */
    public function withDataSets(array $dataSets): StepInterface
    {
        $filteredDataSets = [];

        foreach ($dataSets as $name => $dataSet) {
            if ($dataSet instanceof DataSetInterface) {
                $filteredDataSets[$name] = $dataSet;
            }
        }

        $new = clone $this;
        $new->dataSets = $filteredDataSets;

        return $new;
    }

    /**
     * @param string[] $elementReferences
     *
     * @return StepInterface
     */
    public function withElementReferences(array $elementReferences): StepInterface
    {
        $filteredElementReferences = [];

        foreach ($elementReferences as $elementName => $elementReference) {
            if (is_string($elementReference)) {
                $filteredElementReferences[$elementName] = $elementReference;
            }
        }

        $new = clone $this;
        $new->elementReferences = $filteredElementReferences;

        return $new;
    }
}
