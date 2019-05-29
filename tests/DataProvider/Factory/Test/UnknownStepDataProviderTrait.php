<?php

namespace webignition\BasilParser\Tests\DataProvider\Factory\Test;

use webignition\BasilParser\Builder\StepBuilder;
use webignition\BasilParser\Exception\UnknownStepException;
use webignition\BasilParser\Factory\Test\ConfigurationFactory;
use webignition\BasilParser\Factory\Test\TestFactory;
use webignition\BasilParser\Model\ExceptionContext\ExceptionContext;
use webignition\BasilParser\Model\ExceptionContext\ExceptionContextInterface;

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
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => [
                        ConfigurationFactory::KEY_BROWSER => 'chrome',
                        ConfigurationFactory::KEY_URL => 'http://example.com',
                    ],
                    'step name' => [
                        StepBuilder::KEY_USE => 'step_name',
                    ],
                ],
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
