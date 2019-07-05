<?php

namespace webignition\BasilParser\Factory;

use webignition\BasilModel\DataSet\DataSet;
use webignition\BasilModel\ExceptionContext\ExceptionContextInterface;
use webignition\BasilModel\Identifier\IdentifierInterface;
use webignition\BasilModel\Step\PendingImportResolutionStep;
use webignition\BasilModel\Step\Step;
use webignition\BasilModel\Step\StepInterface;
use webignition\BasilParser\DataStructure\Step as StepData;
use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Factory\Action\ActionFactory;

class StepFactory
{
    /**
     * @var ActionFactory
     */
    private $actionFactory;

    /**
     * @var AssertionFactory
     */
    private $assertionFactory;

    /**
     * @var IdentifierFactory
     */
    private $identifierFactory;

    public function __construct(
        ActionFactory $actionFactory,
        AssertionFactory $assertionFactory,
        IdentifierFactory $identifierFactory
    ) {
        $this->actionFactory = $actionFactory;
        $this->assertionFactory = $assertionFactory;
        $this->identifierFactory = $identifierFactory;
    }

    /**
     * @param StepData $stepData
     *
     * @return StepInterface
     *
     * @throws MalformedPageElementReferenceException
     */
    public function createFromStepData(StepData $stepData): StepInterface
    {
        $actionStrings = $stepData->getActions();
        $assertionStrings = $stepData->getAssertions();

        $actions = [];
        $assertions = [];

        $actionString = '';
        $assertionString = '';

        try {
            foreach ($actionStrings as $actionString) {
                if ('string' === gettype($actionString)) {
                    $actionString = trim($actionString);

                    if ('' !== $actionString) {
                        $actions[] = $this->actionFactory->createFromActionString($actionString);
                    }
                }
            }

            foreach ($assertionStrings as $assertionString) {
                if ('string' === gettype($assertionString)) {
                    $assertionString = trim($assertionString);

                    if ('' !== $assertionString) {
                        $assertions[] = $this->assertionFactory->createFromAssertionString($assertionString);
                    }
                }
            }
        } catch (MalformedPageElementReferenceException $contextAwareException) {
            $contextAwareException->applyExceptionContext([
                ExceptionContextInterface::KEY_CONTENT => $assertionString !== '' ? $assertionString : $actionString,
            ]);

            throw $contextAwareException;
        }

        if ($stepData->getImportName() || $stepData->getDataImportName()) {
            $step = new PendingImportResolutionStep(
                $actions,
                $assertions,
                $stepData->getImportName(),
                $stepData->getDataImportName()
            );
        } else {
            $step = new Step($actions, $assertions);
        }

        $dataArray = $stepData->getDataArray();
        if (!empty($dataArray)) {
            foreach ($dataArray as $key => $dataSetData) {
                $data[$key] = new DataSet($dataSetData);
            }
        }

        if (!empty($data)) {
            $step = $step->withDataSets($data);
        }

        $elementIdentifiers = [];
        foreach ($stepData->getElements() as $elementName => $elementIdentifierString) {
            $elementIdentifier = $this->identifierFactory->create($elementIdentifierString, $elementName);

            if ($elementIdentifier instanceof IdentifierInterface) {
                $elementIdentifiers[] = $elementIdentifier;
            }
        }

        if (!empty($elementIdentifiers)) {
            $step = $step->withElementIdentifiers($elementIdentifiers);
        }

        return $step;
    }
}
