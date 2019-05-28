<?php

namespace webignition\BasilParser\Factory\Test;

use webignition\BasilParser\Builder\StepBuilder;
use webignition\BasilParser\Exception\NonRetrievableStepException;
use webignition\BasilParser\Exception\UnknownStepException;
use webignition\BasilParser\Loader\StepLoader;
use webignition\BasilParser\Model\Test\TestInterface;
use webignition\BasilParser\Provider\DataSet\DeferredDataSetProvider;
use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievableDataProviderException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownDataProviderException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Loader\DataSetLoader;
use webignition\BasilParser\Loader\PageLoader;
use webignition\BasilParser\Model\Test\Test;
use webignition\BasilParser\Provider\Page\DeferredPageProvider;
use webignition\BasilParser\Provider\Step\DeferredStepProvider;

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
    private $pageLoader;
    private $stepBuilder;
    private $dataSetLoader;
    private $stepLoader;

    public function __construct(
        ConfigurationFactory $configurationFactory,
        PageLoader $pageLoader,
        StepBuilder $stepBuilder,
        DataSetLoader $dataSetLoader,
        StepLoader $stepLoader
    ) {
        $this->configurationFactory = $configurationFactory;
        $this->pageLoader = $pageLoader;
        $this->stepBuilder = $stepBuilder;
        $this->dataSetLoader = $dataSetLoader;
        $this->stepLoader = $stepLoader;
    }

    /**
     * @param array $testData
     *
     * @return TestInterface
     *
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievableDataProviderException
     * @throws NonRetrievablePageException
     * @throws UnknownDataProviderException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     * @throws NonRetrievableStepException
     * @throws UnknownStepException
     */
    public function createFromTestData(array $testData)
    {
        $configurationData = $testData[self::KEY_CONFIGURATION] ?? [];
        $importPaths = $testData[self::KEY_IMPORTS] ?? [];

        $configurationData = is_array($configurationData) ? $configurationData : [];
        $importPaths = is_array($importPaths) ? $importPaths : [];

        $stepImportPaths = $importPaths[self::KEY_IMPORTS_STEPS] ?? [];
        $pageImportPaths = $importPaths[self::KEY_IMPORTS_PAGES] ?? [];
        $dataProviderImportPaths = $importPaths[self::KEY_IMPORTS_DATA_PROVIDERS] ?? [];

        $stepNames = array_diff(array_keys($testData), [self::KEY_CONFIGURATION, self::KEY_IMPORTS]);

        $stepProvider = new DeferredStepProvider($this->stepLoader, $stepImportPaths);
        $pageProvider = new DeferredPageProvider($this->pageLoader, $pageImportPaths);
        $dataSetProvider = new DeferredDataSetProvider($this->dataSetLoader, $dataProviderImportPaths);

        $configuration = $this->configurationFactory->createFromConfigurationData($configurationData, $pageProvider);
        $steps = [];

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

        return new Test($configuration, $steps);
    }
}
