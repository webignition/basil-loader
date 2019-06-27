<?php

namespace webignition\BasilParser\Tests\DataProvider\Factory\Test;

use webignition\BasilModel\ExceptionContext\ExceptionContext;
use webignition\BasilModel\ExceptionContext\ExceptionContextInterface;
use webignition\BasilParser\DataStructure\Step as StepData;
use webignition\BasilParser\DataStructure\Test\Configuration as ConfigurationData;
use webignition\BasilParser\DataStructure\Test\Imports as ImportsData;
use webignition\BasilParser\DataStructure\Test\Test as TestData;
use webignition\BasilParser\Exception\NonRetrievableDataProviderException;
use webignition\BasilParser\Tests\Services\FixturePathFinder;

trait NonRetrievableDataProviderDataProviderTrait
{
    public function createFromTestDataThrowsNonRetrievableDataProviderExceptionDataProvider(): array
    {
        // NonRetrievableDataProviderException
        //   thrown when trying to load a data provider that does not exist or which is not valid yaml
        //
        //   cases:
        //   - test.data references data provider that cannot be loaded
        //   - test.data references data provider containing invalid yaml
        //
        //   context to be applied in:
        //   - TestFactory calling StepBuilder::build()

        return [
            'NonRetrievableDataProviderException: test.data references data provider that cannot be loaded' => [
                'name' => 'test name',
                'testData' => new TestData([
                    TestData::KEY_CONFIGURATION => [
                        ConfigurationData::KEY_BROWSER => 'chrome',
                        ConfigurationData::KEY_URL => 'http://example.com',
                    ],
                    TestData::KEY_IMPORTS => [
                        ImportsData::KEY_STEPS => [
                            'step_import_name' => FixturePathFinder::find('Step/data-parameters.yml'),
                        ],
                        ImportsData::KEY_DATA_PROVIDERS => [
                            'data_provider_name' => 'DataProvider/non-existent.yml'
                        ],
                    ],
                    'step name' => [
                        StepData::KEY_USE => 'step_import_name',
                        StepData::KEY_DATA => 'data_provider_name',
                    ],
                ]),
                'expectedException' => NonRetrievableDataProviderException::class,
                'expectedExceptionMessage' =>
                    'Cannot retrieve data provider "data_provider_name" from "DataProvider/non-existent.yml"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                ])
            ],
            'NonRetrievableDataProviderException: test.data references data provider containing invalid yaml' => [
                'name' => 'test name',
                'testData' => new TestData([
                    TestData::KEY_CONFIGURATION => [
                        ConfigurationData::KEY_BROWSER => 'chrome',
                        ConfigurationData::KEY_URL => 'http://example.com',
                    ],
                    TestData::KEY_IMPORTS => [
                        ImportsData::KEY_STEPS => [
                            'step_import_name' => FixturePathFinder::find('Step/data-parameters.yml'),
                        ],
                        ImportsData::KEY_DATA_PROVIDERS => [
                            'data_provider_name' => $this->invalidYamlPath,
                        ],
                    ],
                    'step name' => [
                        StepData::KEY_USE => 'step_import_name',
                        StepData::KEY_DATA => 'data_provider_name',
                    ],
                ]),
                'expectedException' => NonRetrievableDataProviderException::class,
                'expectedExceptionMessage' =>
                    'Cannot retrieve data provider "data_provider_name" from "' . $this->invalidYamlPath . '"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                ])
            ],
        ];
    }
}
