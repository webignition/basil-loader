<?php

namespace webignition\BasilParser\Tests\DataProvider\Factory\Test;

use webignition\BasilParser\Exception\UnknownDataProviderException;
use webignition\BasilParser\Factory\Test\ConfigurationFactory;
use webignition\BasilParser\Factory\Test\TestFactory;
use webignition\BasilParser\Model\ExceptionContext\ExceptionContext;
use webignition\BasilParser\Model\ExceptionContext\ExceptionContextInterface;
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
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => [
                        ConfigurationFactory::KEY_BROWSER => 'chrome',
                        ConfigurationFactory::KEY_URL => 'http://example.com',
                    ],
                    TestFactory::KEY_IMPORTS => [
                        TestFactory::KEY_IMPORTS_STEPS => [
                            'step_import_name' => FixturePathFinder::find('Step/data-parameters.yml'),
                        ],
                    ],
                    'step name' => [
                        TestFactory::KEY_TEST_USE => 'step_import_name',
                        TestFactory::KEY_TEST_DATA => 'data_provider_import_name',
                    ],
                ],
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
