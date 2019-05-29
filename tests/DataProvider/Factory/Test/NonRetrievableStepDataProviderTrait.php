<?php

namespace webignition\BasilParser\Tests\DataProvider\Factory\Test;

use webignition\BasilParser\Exception\NonRetrievableStepException;
use webignition\BasilParser\Factory\Test\ConfigurationFactory;
use webignition\BasilParser\Factory\Test\TestFactory;
use webignition\BasilParser\Model\ExceptionContext\ExceptionContext;
use webignition\BasilParser\Model\ExceptionContext\ExceptionContextInterface;

trait NonRetrievableStepDataProviderTrait
{
    public function createFromTestDataThrowsNonRetrievableStepExceptionDataProvider(): array
    {
        // NonRetrievableStepException
        //   thrown when trying to load a step that does not exist or which is not valid yaml
        //
        //   cases:
        //   - step.use references step that does not exist
        //   - step.use references step that contains invalid yaml
        //
        //   context to be applied in:
        //   - TestFactory calling StepBuilder::build()

        return [
            'NonRetrievableStepException: step.uses references step that does not exist' => [
                'name' => 'test name',
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => [
                        ConfigurationFactory::KEY_BROWSER => 'chrome',
                        ConfigurationFactory::KEY_URL => 'http://example.com',
                    ],
                    TestFactory::KEY_IMPORTS => [
                        TestFactory::KEY_IMPORTS_STEPS => [
                            'step_import_name' => 'Step/non-existent.yml',
                        ],
                    ],
                    'step one' => [
                        TestFactory::KEY_TEST_USE => 'step_import_name',
                    ],
                ],
                'expectedException' => NonRetrievableStepException::class,
                'expectedExceptionMessage' => 'Cannot retrieve step "step_import_name" from "Step/non-existent.yml"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step one',
                ])
            ],
            'NonRetrievableStepException: step.uses references step contains invalid yaml' => [
                'name' => 'test name',
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => [
                        ConfigurationFactory::KEY_BROWSER => 'chrome',
                        ConfigurationFactory::KEY_URL => 'http://example.com',
                    ],
                    TestFactory::KEY_IMPORTS => [
                        TestFactory::KEY_IMPORTS_STEPS => [
                            'step_import_name' => $this->invalidYamlPath,
                        ],
                    ],
                    'step one' => [
                        TestFactory::KEY_TEST_USE => 'step_import_name',
                    ],
                ],
                'expectedException' => NonRetrievableStepException::class,
                'expectedExceptionMessage' =>
                    'Cannot retrieve step "step_import_name" from "' . $this->invalidYamlPath . '"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step one',
                ])
            ],
        ];
    }
}
