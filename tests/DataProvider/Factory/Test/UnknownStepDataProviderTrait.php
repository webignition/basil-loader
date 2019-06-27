<?php

namespace webignition\BasilParser\Tests\DataProvider\Factory\Test;

use webignition\BasilModel\ExceptionContext\ExceptionContext;
use webignition\BasilModel\ExceptionContext\ExceptionContextInterface;
use webignition\BasilParser\DataStructure\Step as StepData;
use webignition\BasilParser\DataStructure\Test\Configuration as ConfigurationData;
use webignition\BasilParser\DataStructure\Test\Test as TestData;
use webignition\BasilParser\Exception\UnknownStepException;

trait UnknownStepDataProviderTrait
{
    public function createFromTestDataThrowsUnknownStepExceptionDataProvider(): array
    {
        // UnknownStepException
        //   thrown when trying to reference a step not defined within a collection
        //
        //   cases:
        //   - step.use references step not defined within a collection
        //
        //   context to be applied in:
        //   - TestFactory calling StepBuilder::build()

        return [
            'UnknownStepException: step.use references step not defined within a collection' => [
                'name' => 'test name',
                'testData' => new TestData([
                    TestData::KEY_CONFIGURATION => [
                        ConfigurationData::KEY_BROWSER => 'chrome',
                        ConfigurationData::KEY_URL => 'http://example.com',
                    ],
                    'step name' => [
                        StepData::KEY_USE => 'step_name',
                    ],
                ]),
                'expectedException' => UnknownStepException::class,
                'expectedExceptionMessage' => 'Unknown step "step_name"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                ])
            ],
        ];
    }
}
