<?php

namespace webignition\BasilParser\Factory\Test;

use webignition\BasilModel\ExceptionContext\ExceptionContextInterface;
use webignition\BasilModel\Test\TestInterface;
use webignition\BasilModel\Test\Test;
use webignition\BasilParser\Builder\StepBuilder;
use webignition\BasilParser\DataStructure\Step;
use webignition\BasilParser\DataStructure\Test\Test as TestData;
use webignition\BasilParser\Exception\NonRetrievableStepException;
use webignition\BasilParser\Exception\UnknownStepException;
use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievableDataProviderException;
use webignition\BasilParser\Exception\UnknownDataProviderException;
use webignition\BasilParser\Provider\DataSet\Factory as DataSetProviderFactory;
use webignition\BasilParser\Provider\Step\Factory as StepProviderFactory;

class TestFactory
{
    private $configurationFactory;
    private $stepBuilder;
    private $stepProviderFactory;
    private $dataSetProviderFactory;

    public function __construct(
        ConfigurationFactory $configurationFactory,
        StepBuilder $stepBuilder,
        StepProviderFactory $stepProviderFactory,
        DataSetProviderFactory $dataSetProviderFactory
    ) {
        $this->configurationFactory = $configurationFactory;
        $this->stepBuilder = $stepBuilder;
        $this->stepProviderFactory = $stepProviderFactory;
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
     * @throws NonRetrievableStepException
     * @throws UnknownDataProviderException
     * @throws UnknownStepException
     */
    public function createFromTestData(string $name, TestData $testData)
    {
        $imports = $testData->getImports();

        $stepProvider = $this->stepProviderFactory->createDeferredStepProvider($imports->getStepPaths());
        $dataSetProvider = $this->dataSetProviderFactory->createDeferredDataSetProvider(
            $imports->getDataProviderPaths()
        );

        $configuration = $this->configurationFactory->createFromConfigurationData($testData->getConfiguration());

        $steps = [];

        /* @var Step $stepData */
        foreach ($testData->getSteps() as $stepName => $stepData) {
            try {
                $step =  $this->stepBuilder->build(
                    $stepData,
                    $stepProvider,
                    $dataSetProvider
                );
            } catch (MalformedPageElementReferenceException |
                NonRetrievableDataProviderException |
                NonRetrievableStepException |
                UnknownDataProviderException |
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
