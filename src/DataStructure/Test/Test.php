<?php

namespace webignition\BasilParser\DataStructure\Test;

use webignition\BasilParser\DataStructure\AbstractDataStructure;

class Test extends AbstractDataStructure
{
    const KEY_CONFIGURATION = 'config';
    const KEY_IMPORTS = 'imports';
//    const KEY_IMPORTS_STEPS = 'steps';
//    const KEY_IMPORTS_PAGES = 'pages';
//    const KEY_IMPORTS_DATA_PROVIDERS = 'data_providers';
//    const KEY_TEST_USE = 'use';
//    const KEY_TEST_DATA = 'data';

    public function getConfiguration(): Configuration
    {
        return new Configuration($this->data[self::KEY_CONFIGURATION] ?? []);
    }

    public function getImports(): Imports
    {
        return new Imports($this->data[self::KEY_IMPORTS] ?? []);
    }

    public function getSteps(): array
    {
        $stepNames = array_diff(array_keys($this->data), [self::KEY_CONFIGURATION, self::KEY_IMPORTS]);

        $steps = [];

        foreach ($stepNames as $stepName) {
            $steps[$stepName] = $this->data[$stepName];
        }

        return $steps;
    }
}
