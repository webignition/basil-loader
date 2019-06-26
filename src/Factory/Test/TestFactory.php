<?php

namespace webignition\BasilParser\Factory\Test;

use webignition\BasilParser\Builder\StepBuilder;
use webignition\BasilParser\DataStructure\Step;
use webignition\BasilParser\DataStructure\Test\Test as TestData;
use webignition\BasilParser\Exception\NonRetrievableStepException;
use webignition\BasilParser\Exception\UnknownStepException;
use webignition\BasilParser\Model\ExceptionContext\ExceptionContextInterface;
use webignition\BasilParser\Model\Test\TestInterface;
use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievableDataProviderException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownDataProviderException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Model\Test\Test;
use webignition\BasilParser\Provider\DataSet\Factory as DataSetProviderFactory;
use webignition\BasilParser\Provider\Page\Factory as PageProviderFactory;
use webignition\BasilParser\Provider\Step\Factory as StepProviderFactory;

class TestFactory
{
    private $configurationFactory;
    private $stepBuilder;
    private $stepProviderFactory;
    private $pageProviderFactory;
    private $dataSetProviderFactory;

    public function __construct(
        ConfigurationFactory $configurationFactory,
        StepBuilder $stepBuilder,
        StepProviderFactory $stepProviderFactory,
        PageProviderFactory $pageProviderFactory,
        DataSetProviderFactory $dataSetProviderFactory
    ) {
        $this->configurationFactory = $configurationFactory;
        $this->stepBuilder = $stepBuilder;
        $this->stepProviderFactory = $stepProviderFactory;
        $this->pageProviderFactory = $pageProviderFactory;
        $this->dataSetProviderFactory = $dataSetProviderFactory;
    }

    /**
     * @param string $name
     * @param TestData $testData
     *
     * @return TestInterface
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
    public function createFromTestData(string $name, TestData $testData)
    {
        $imports = $testData->getImports();

        $stepProvider = $this->stepProviderFactory->createDeferredStepProvider($imports->getStepPaths());
        $pageProvider = $this->pageProviderFactory->createDeferredPageProvider($imports->getPagePaths());
        $dataSetProvider = $this->dataSetProviderFactory->createDeferredDataSetProvider(
            $imports->getDataProviderPaths()
        );

        try {
            $configuration = $this->configurationFactory->createFromConfigurationData(
                $testData->getConfiguration(),
                $pageProvider
            );
        } catch (NonRetrievablePageException | UnknownPageException $nonRetrievablePageException) {
            $nonRetrievablePageException->applyExceptionContext([
                ExceptionContextInterface::KEY_TEST_NAME => $name,
            ]);

            throw $nonRetrievablePageException;
        }

        $steps = [];

        /* @var Step $stepData */
        foreach ($testData->getSteps() as $stepName => $stepData) {
            try {
                $step =  $this->stepBuilder->build(
                    $stepData,
                    $stepProvider,
                    $dataSetProvider,
                    $pageProvider
                );
            } catch (MalformedPageElementReferenceException |
                NonRetrievableDataProviderException |
                NonRetrievablePageException |
                NonRetrievableStepException |
                UnknownDataProviderException |
                UnknownPageElementException |
                UnknownPageException |
                UnknownStepException $contextAwareException
            ) {
                $contextAwareException->applyExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => $name,
                    ExceptionContextInterface::KEY_STEP_NAME => $stepName,
                ]);

                throw $contextAwareException;
            }

            $steps[$stepName] = $step;
        }

        return new Test($name, $configuration, $steps);
    }
}
