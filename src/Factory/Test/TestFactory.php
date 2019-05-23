<?php

namespace webignition\BasilParser\Factory\Test;

use webignition\BasilParser\Factory\StepFactory;
use webignition\BasilParser\Model\Test\Test;

class TestFactory
{
    const KEY_CONFIGURATION = 'config';
    const KEY_IMPORTS = 'imports';

    private $importCollectionFactory;
    private $configurationFactory;
    private $stepFactory;

    public function __construct()
    {
        $this->importCollectionFactory = new ImportCollectionFactory();
        $this->configurationFactory = new ConfigurationFactory();
        $this->stepFactory = new StepFactory();
    }

    public function createFromTestData(array $testData)
    {
        $configurationData = $testData[self::KEY_CONFIGURATION] ?? [];
        $importsData = $testData[self::KEY_IMPORTS] ?? [];

        $configurationData = is_array($configurationData) ? $configurationData : [];
        $importsData = is_array($importsData) ? $importsData : [];

        $stepNames = array_diff(array_keys($testData), [self::KEY_CONFIGURATION, self::KEY_IMPORTS]);

        $configuration = $this->configurationFactory->createFromConfigurationData($configurationData);
        $steps = [];

        foreach ($stepNames as $stepName) {
            $step = $this->stepFactory->createFromStepData($testData[$stepName]);

            $steps[$stepName] = $step;
        }

        return new Test($configuration, $steps);
    }
}
