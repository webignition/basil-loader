<?php

namespace webignition\BasilParser\Provider\Step;

use webignition\BasilModel\Step\StepInterface;
use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievableDataProviderException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\NonRetrievableStepException;
use webignition\BasilParser\Exception\UnknownDataProviderException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Exception\UnknownStepException;
use webignition\BasilParser\Exception\YamlLoaderException;
use webignition\BasilParser\Loader\StepLoader;
use webignition\BasilParser\Provider\DataSet\DataSetProviderInterface;
use webignition\BasilParser\Provider\Page\PageProviderInterface;

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
     * @param StepProviderInterface $stepProvider
     * @param DataSetProviderInterface $dataSetProvider
     * @param PageProviderInterface $pageProvider
     *
     * @return StepInterface
     *
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievableDataProviderException
     * @throws NonRetrievablePageException
     * @throws NonRetrievableStepException
     * @throws UnknownDataProviderException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
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
            $step = $this->retrieveStep($importName, $stepProvider, $dataSetProvider, $pageProvider);
            $this->steps[$importName] = $step;
        }

        return $step;
    }

    /**
     * @param string $importName
     * @param StepProviderInterface $stepProvider
     * @param DataSetProviderInterface $dataSetProvider
     * @param PageProviderInterface $pageProvider
     *
     * @return StepInterface
     *
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievableDataProviderException
     * @throws NonRetrievablePageException
     * @throws NonRetrievableStepException
     * @throws UnknownDataProviderException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     * @throws UnknownStepException
     */
    private function retrieveStep(
        string $importName,
        StepProviderInterface $stepProvider,
        DataSetProviderInterface $dataSetProvider,
        PageProviderInterface $pageProvider
    ): StepInterface {
        $importPath = $this->importPaths[$importName] ?? null;

        if (null === $importPath) {
            throw new UnknownStepException($importName);
        }

        try {
            return $this->stepLoader->load($importPath, $stepProvider, $dataSetProvider, $pageProvider);
        } catch (YamlLoaderException $yamlLoaderException) {
            throw new NonRetrievableStepException($importName, $importPath, $yamlLoaderException);
        }
    }
}
