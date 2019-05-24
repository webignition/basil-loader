<?php

namespace webignition\BasilParser\Factory\Test;

use webignition\BasilParser\Builder\StepBuilder;
use webignition\BasilParser\Model\Test\Test;

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

    public function __construct(ConfigurationFactory $configurationFactory, StepBuilder $stepBuilder)
    {
        $this->configurationFactory = $configurationFactory;
        $this->stepBuilder = $stepBuilder;
    }

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

        foreach ($stepNames as $stepName) {
            $stepData = $testData[$stepName];

            $step = $this->stepBuilder->build(
                $stepName,
                $stepData,
                $stepImportPaths,
                $pageImportPaths,
                $dataProviderImportPaths
            );

            $steps[$stepName] = $step;
        }

        return new Test($configuration, $steps);
    }
}
