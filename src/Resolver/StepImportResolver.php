<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Resolver;

use webignition\BasilModels\Model\Step\StepInterface;
use webignition\BasilModels\Provider\DataSet\DataSetProviderInterface;
use webignition\BasilModels\Provider\Exception\UnknownItemException;
use webignition\BasilModels\Provider\Step\StepProviderInterface;

class StepImportResolver
{
    public static function createResolver(): StepImportResolver
    {
        return new StepImportResolver();
    }

    /**
     * @param string[] $handledImportNames
     *
     * @throws CircularStepImportException
     * @throws UnknownItemException
     */
    public function resolveStepImport(
        StepInterface $step,
        StepProviderInterface $stepProvider,
        array $handledImportNames = []
    ): StepInterface {
        $importName = $step->getImportName();

        if (null !== $importName) {
            if (in_array($importName, $handledImportNames)) {
                throw new CircularStepImportException($importName);
            }

            $parentStep = $stepProvider->find($importName);

            if ($parentStep->requiresImportResolution()) {
                $handledImportNames[] = $importName;
                $parentStep = $this->resolveStepImport($parentStep, $stepProvider, $handledImportNames);
            }

            $step = $step
                ->withPrependedActions($parentStep->getActions())
                ->withPrependedAssertions($parentStep->getAssertions())
            ;

            $step = $step->removeImportName();
        }

        return $step;
    }

    /**
     * @throws UnknownItemException
     */
    public function resolveDataProviderImport(
        StepInterface $step,
        DataSetProviderInterface $dataSetProvider
    ): StepInterface {
        $dataProviderImportName = $step->getDataImportName();

        if (null !== $dataProviderImportName) {
            $step = $step->withData($dataSetProvider->find($dataProviderImportName));
            $step = $step->removeDataImportName();
        }

        return $step;
    }
}
