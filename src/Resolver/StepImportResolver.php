<?php

namespace webignition\BasilParser\Resolver;

use webignition\BasilModel\Step\PendingImportResolutionStep;
use webignition\BasilModel\Step\PendingImportResolutionStepInterface;
use webignition\BasilModel\Step\StepInterface;
use webignition\BasilModelFactory\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\CircularStepImportException;
use webignition\BasilParser\Exception\NonRetrievableDataProviderException;
use webignition\BasilParser\Exception\NonRetrievableStepException;
use webignition\BasilParser\Exception\UnknownDataProviderException;
use webignition\BasilParser\Exception\UnknownStepException;
use webignition\BasilParser\Provider\DataSet\DataSetProviderInterface;
use webignition\BasilParser\Provider\Step\StepProviderInterface;

class StepImportResolver
{
    public static function createResolver(): StepImportResolver
    {
        return new StepImportResolver();
    }

    /**
     * @param StepInterface $step
     * @param StepProviderInterface $stepProvider
     * @param array $handledImportNames
     *
     * @return StepInterface
     *
     * @throws CircularStepImportException
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievableStepException
     * @throws UnknownStepException
     */
    public function resolveStepImport(
        StepInterface $step,
        StepProviderInterface $stepProvider,
        array $handledImportNames = []
    ): StepInterface {
        if ($step instanceof PendingImportResolutionStepInterface) {
            $importName = $step->getImportName();

            if ('' !== $importName) {
                if (in_array($importName, $handledImportNames)) {
                    throw new CircularStepImportException($importName);
                }

                $parentStep = $stepProvider->findStep($importName);

                if ($parentStep instanceof PendingImportResolutionStepInterface) {
                    $handledImportNames[] = $importName;
                    $parentStep = $this->resolveStepImport($parentStep, $stepProvider, $handledImportNames);
                }

                $step = $step
                    ->withPrependedActions($parentStep->getActions())
                    ->withPrependedAssertions($parentStep->getAssertions());
            }

            if ($step instanceof PendingImportResolutionStep) {
                $step = $step->clearImportName();

                if (!$step->requiresResolution()) {
                    $step = $step->getStep();
                }
            }
        }

        return $step;
    }

    /**
     * @param StepInterface $step
     * @param DataSetProviderInterface $dataSetProvider
     *
     * @return StepInterface
     *
     * @throws NonRetrievableDataProviderException
     * @throws UnknownDataProviderException
     */
    public function resolveDataProviderImport(
        StepInterface $step,
        DataSetProviderInterface $dataSetProvider
    ): StepInterface {
        if ($step instanceof PendingImportResolutionStepInterface) {
            $dataProviderImportName = $step->getDataProviderImportName();

            if ('' !== $dataProviderImportName) {
                $step = $step->withDataSetCollection($dataSetProvider->findDataSetCollection($dataProviderImportName));
            }

            if ($step instanceof PendingImportResolutionStep) {
                $step = $step->clearDataProviderImportName();

                if (!$step->requiresResolution()) {
                    $step = $step->getStep();
                }
            }
        }

        return $step;
    }
}