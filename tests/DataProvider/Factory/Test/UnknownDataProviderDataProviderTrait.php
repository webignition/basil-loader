<?php

namespace webignition\BasilParser\Tests\DataProvider\Factory\Test;

use webignition\BasilModel\ExceptionContext\ExceptionContext;
use webignition\BasilModel\ExceptionContext\ExceptionContextInterface;
use webignition\BasilParser\DataStructure\Step as StepData;
use webignition\BasilParser\DataStructure\Test\Configuration as ConfigurationData;
use webignition\BasilParser\DataStructure\Test\Imports as ImportsData;
use webignition\BasilParser\DataStructure\Test\Test as TestData;
use webignition\BasilParser\Exception\UnknownDataProviderException;
use webignition\BasilParser\Tests\Services\FixturePathFinder;

trait UnknownDataProviderDataProviderTrait
{
    public function createFromTestDataThrowsUnknownDataProviderExceptionDataProvider(): array
    {
        // UnknownDataProviderException
        //   thrown when trying to access a data provider not defined within a collection
        //
        //   cases:
        //   - test.data references a data provider that has not been defined
        //
        //   context to be applied in:
        //   - TestFactory calling StepBuilder::build()

        return [
            'UnknownDataProviderException: test.data references a data provider that has not been defined' => [
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
                    ],
                    'step name' => [
                        StepData::KEY_USE => 'step_import_name',
                        StepData::KEY_DATA => 'data_provider_import_name',
                    ],
                ]),
                'expectedException' => UnknownDataProviderException::class,
                'expectedExceptionMessage' => 'Unknown data provider "data_provider_import_name"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                ])
            ],
        ];
    }
}
