<?php

namespace webignition\BasilParser\Builder;

use webignition\BasilParser\Factory\StepFactory;
use webignition\BasilParser\Loader\StepLoader;
use webignition\BasilParser\Loader\YamlLoaderException;
use webignition\BasilParser\Model\Step\StepInterface;

class StepBuilder
{
    const KEY_USE = 'use';
    const KEY_DATA = 'data';

    private $stepFactory;
    private $stepLoader;

    public function __construct(StepFactory $stepFactory, StepLoader $stepLoader)
    {
        $this->stepFactory = $stepFactory;
        $this->stepLoader = $stepLoader;
    }

    /**
     * @param string $stepName
     * @param array $stepData
     * @param array $stepImportPaths
     * @param array $dataProviderImportPaths
     *
     * @return StepInterface
     *
     * @throws UnknownStepImportException
     * @throws YamlLoaderException
     */
    public function build(string $stepName, array $stepData, array $stepImportPaths, array $dataProviderImportPaths)
    {
        $importName = $stepData[self::KEY_USE] ?? null;
        if (null === $importName) {
            $step = $this->stepFactory->createFromStepData($stepData);
        } else {
            $importPath = $stepImportPaths[$importName] ?? null;

            if (null === $importPath) {
                throw new UnknownStepImportException($stepName, $importName, $stepImportPaths);
            }

            $step = $this->stepLoader->load($importPath);
        }

        $data = $stepData[self::KEY_DATA] ?? null;
        if (null !== $data) {
            if (is_array($data)) {
                $step = $step->withDataSets($data);
            }

//            if (is_string($data) {
//
//            }
        }

        return $step;
    }
}
