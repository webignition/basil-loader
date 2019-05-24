<?php

namespace webignition\BasilParser\Factory\Test;

use webignition\BasilParser\Builder\StepBuilder;
use webignition\BasilParser\Builder\StepBuilderInvalidPageElementReferenceException;
use webignition\BasilParser\Builder\StepBuilderUnknownDataProviderImportException;
use webignition\BasilParser\Builder\StepBuilderUnknownPageElementException;
use webignition\BasilParser\Builder\StepBuilderUnknownStepImportException;
use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Loader\PageLoader;
use webignition\BasilParser\Loader\YamlLoaderException;
use webignition\BasilParser\Model\Test\Test;
use webignition\BasilParser\PageCollection\DeferredPageCollection;

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

    public function __construct(
        ConfigurationFactory $configurationFactory,
        PageLoader $pageLoader,
        StepBuilder $stepBuilder
    ) {
        $this->configurationFactory = $configurationFactory;
        $this->pageLoader = $pageLoader;
        $this->stepBuilder = $stepBuilder;
    }

    /**
     * @param array $testData
     * @return Test
     * @throws StepBuilderInvalidPageElementReferenceException
     * @throws StepBuilderUnknownDataProviderImportException
     * @throws StepBuilderUnknownPageElementException
     * @throws StepBuilderUnknownStepImportException
     * @throws MalformedPageElementReferenceException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     * @throws YamlLoaderException
     * @throws NonRetrievablePageException
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

        $configuration = $this->configurationFactory->createFromConfigurationData($configurationData);
        $steps = [];

        $pages = new DeferredPageCollection($this->pageLoader, $pageImportPaths);

        foreach ($stepNames as $stepName) {
            $stepData = $testData[$stepName];

            $step = $this->stepBuilder->build(
                $stepName,
                $stepData,
                $stepImportPaths,
                $dataProviderImportPaths,
                $pages
            );

            $steps[$stepName] = $step;
        }

        return new Test($configuration, $steps);
    }
}
