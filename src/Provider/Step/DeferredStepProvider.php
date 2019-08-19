<?php

namespace webignition\BasilParser\Provider\Step;

use webignition\BasilModel\Step\StepInterface;
use webignition\BasilModelFactory\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievableStepException;
use webignition\BasilParser\Exception\UnknownStepException;
use webignition\BasilParser\Exception\YamlLoaderException;
use webignition\BasilParser\Loader\StepLoader;

class DeferredStepProvider implements StepProviderInterface
{
    private $stepLoader;
    private $importPaths;
    private $steps = [];

    public function __construct(StepLoader $stepLoader, array $importPaths)
    {
        $this->stepLoader = $stepLoader;
        $this->importPaths = $importPaths;
    }

    /**
     * @param string $importName
     *
     * @return StepInterface
     *
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievableStepException
     * @throws UnknownStepException
     */
    public function findStep(string $importName): StepInterface
    {
        $step = $this->steps[$importName] ?? null;

        if (null === $step) {
            $step = $this->retrieveStep($importName);
            $this->steps[$importName] = $step;
        }

        return $step;
    }

    /**
     * @param string $importName
     *
     * @return StepInterface
     *
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievableStepException
     * @throws UnknownStepException
     */
    private function retrieveStep(string $importName): StepInterface
    {
        $importPath = $this->importPaths[$importName] ?? null;

        if (null === $importPath) {
            throw new UnknownStepException($importName);
        }

        try {
            return $this->stepLoader->load($importPath);
        } catch (YamlLoaderException $yamlLoaderException) {
            throw new NonRetrievableStepException($importName, $importPath, $yamlLoaderException);
        }
    }
}
