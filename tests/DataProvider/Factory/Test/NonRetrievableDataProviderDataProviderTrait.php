<?php

namespace webignition\BasilParser\Tests\DataProvider\Factory\Test;

use webignition\BasilParser\Exception\NonRetrievableDataProviderException;
use webignition\BasilParser\Factory\Test\ConfigurationFactory;
use webignition\BasilParser\Factory\Test\TestFactory;
use webignition\BasilParser\Model\ExceptionContext\ExceptionContext;
use webignition\BasilParser\Model\ExceptionContext\ExceptionContextInterface;
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
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => [
                        ConfigurationFactory::KEY_BROWSER => 'chrome',
                        ConfigurationFactory::KEY_URL => 'http://example.com',
                    ],
                    TestFactory::KEY_IMPORTS => [
                        TestFactory::KEY_IMPORTS_STEPS => [
                            'step_import_name' => FixturePathFinder::find('Step/data-parameters.yml'),
                        ],
                        TestFactory::KEY_IMPORTS_DATA_PROVIDERS => [
                            'data_provider_name' => 'DataProvider/non-existent.yml'
                        ],
                    ],
                    'step name' => [
                        TestFactory::KEY_TEST_USE => 'step_import_name',
                        TestFactory::KEY_TEST_DATA => 'data_provider_name',
                    ],
                ],
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
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => [
                        ConfigurationFactory::KEY_BROWSER => 'chrome',
                        ConfigurationFactory::KEY_URL => 'http://example.com',
                    ],
                    TestFactory::KEY_IMPORTS => [
                        TestFactory::KEY_IMPORTS_STEPS => [
                            'step_import_name' => FixturePathFinder::find('Step/data-parameters.yml'),
                        ],
                        TestFactory::KEY_IMPORTS_DATA_PROVIDERS => [
                            'data_provider_name' => $this->invalidYamlPath,
                        ],
                    ],
                    'step name' => [
                        TestFactory::KEY_TEST_USE => 'step_import_name',
                        TestFactory::KEY_TEST_DATA => 'data_provider_name',
                    ],
                ],
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
