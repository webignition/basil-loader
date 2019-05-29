<?php

namespace webignition\BasilParser\Factory\Test;

use webignition\BasilParser\Builder\StepBuilder;
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
    const KEY_CONFIGURATION = 'config';
    const KEY_IMPORTS = 'imports';
    const KEY_IMPORTS_STEPS = 'steps';
    const KEY_IMPORTS_PAGES = 'pages';
    const KEY_IMPORTS_DATA_PROVIDERS = 'data_providers';
    const KEY_TEST_USE = 'use';
    const KEY_TEST_DATA = 'data';

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
     * @param array $testData
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
    public function createFromTestData(string $name, array $testData)
    {
        $configurationData = $testData[self::KEY_CONFIGURATION] ?? [];
        $importPaths = $testData[self::KEY_IMPORTS] ?? [];

        $configurationData = is_array($configurationData) ? $configurationData : [];
        $importPaths = is_array($importPaths) ? $importPaths : [];

        $stepImportPaths = $importPaths[self::KEY_IMPORTS_STEPS] ?? [];
        $pageImportPaths = $importPaths[self::KEY_IMPORTS_PAGES] ?? [];
        $dataProviderImportPaths = $importPaths[self::KEY_IMPORTS_DATA_PROVIDERS] ?? [];

        $stepNames = array_diff(array_keys($testData), [self::KEY_CONFIGURATION, self::KEY_IMPORTS]);

        $stepProvider = $this->stepProviderFactory->createDeferredStepProvider($stepImportPaths);
        $pageProvider = $this->pageProviderFactory->createDeferredPageProvider($pageImportPaths);
        $dataSetProvider = $this->dataSetProviderFactory->createDeferredDataSetProvider($dataProviderImportPaths);

        $configuration = $this->configurationFactory->createFromConfigurationData($configurationData, $pageProvider);
        $steps = [];

        try {
            foreach ($stepNames as $stepName) {
                $stepData = $testData[$stepName];

                $step = $this->stepBuilder->build(
                    $stepData,
                    $stepProvider,
                    $dataSetProvider,
                    $pageProvider
                );

                $steps[$stepName] = $step;
            }
        } catch (MalformedPageElementReferenceException $malformedPageElementReferenceException) {
            $malformedPageElementReferenceException->applyExceptionContext([
                ExceptionContextInterface::KEY_TEST_NAME => $name,
                ExceptionContextInterface::KEY_STEP_NAME => isset($stepName) ? $stepName : '',
            ]);

            throw $malformedPageElementReferenceException;
        } catch (NonRetrievableDataProviderException $nonRetrievableDataProviderException) {
            $nonRetrievableDataProviderException->applyExceptionContext([
                ExceptionContextInterface::KEY_TEST_NAME => $name,
                ExceptionContextInterface::KEY_STEP_NAME => isset($stepName) ? $stepName : '',
            ]);

            throw $nonRetrievableDataProviderException;
        }

        return new Test($name, $configuration, $steps);
    }
}
