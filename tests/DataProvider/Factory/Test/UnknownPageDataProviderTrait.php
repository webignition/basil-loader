<?php

namespace webignition\BasilParser\Tests\DataProvider\Factory\Test;

use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Factory\StepFactory;
use webignition\BasilParser\Factory\Test\ConfigurationFactory;
use webignition\BasilParser\Factory\Test\TestFactory;
use webignition\BasilParser\Model\ExceptionContext\ExceptionContext;
use webignition\BasilParser\Model\ExceptionContext\ExceptionContextInterface;

trait UnknownPageDataProviderTrait
{
    public function createFromTestDataThrowsUnknownPageExceptionDataProvider(): array
    {
        // UnknownPageException
        //   thrown when trying to reference a page not defined within a collection
        //
        //   cases:
        //   - config.url references page not defined within a collection
        //   - assertion string references page not defined within a collection
        //   - action string reference page not defined within a collection
        //
        //   context to be applied in:
        //   - TestFactory calling ConfigurationFactory::createFromConfigurationData()
        //   - TestFactory calling StepBuilder::build()
        //   - StepFactory calling ActionFactory::createFromActionString()
        //   - StepFactory calling AssertionFactory::createFromAssertionString()

        return [
            'UnknownPageException: config.url references page not defined within a collection' => [
                'name' => 'test name',
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => [
                        ConfigurationFactory::KEY_BROWSER => 'chrome',
                        ConfigurationFactory::KEY_URL => 'page_import_name.url',
                    ],
                ],
                'expectedException' => UnknownPageException::class,
                'expectedExceptionMessage' => 'Unknown page "page_import_name"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                ])
            ],
            'UnknownPageException: assertion string references page not defined within a collection' => [
                'name' => 'test name',
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => [
                        ConfigurationFactory::KEY_BROWSER => 'chrome',
                        ConfigurationFactory::KEY_URL => 'http://example.com',
                    ],
                    'step name' => [
                        StepFactory::KEY_ASSERTIONS => [
                            'page_import_name.elements.element_name exists'
                        ],
                    ],
                ],
                'expectedException' => UnknownPageException::class,
                'expectedExceptionMessage' => 'Unknown page "page_import_name"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                    ExceptionContextInterface::KEY_CONTENT => 'page_import_name.elements.element_name exists',
                ])
            ],
            'UnknownPageException: action string references page not defined within a collection' => [
                'name' => 'test name',
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => [
                        ConfigurationFactory::KEY_BROWSER => 'chrome',
                        ConfigurationFactory::KEY_URL => 'http://example.com',
                    ],
                    'step name' => [
                        StepFactory::KEY_ACTIONS => [
                            'click page_import_name.elements.element_name'
                        ],
                    ],
                ],
                'expectedException' => UnknownPageException::class,
                'expectedExceptionMessage' => 'Unknown page "page_import_name"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                    ExceptionContextInterface::KEY_CONTENT => 'click page_import_name.elements.element_name',
                ])
            ],
        ];
    }
}
