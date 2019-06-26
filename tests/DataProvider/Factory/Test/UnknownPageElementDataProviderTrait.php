<?php

namespace webignition\BasilParser\Tests\DataProvider\Factory\Test;

use webignition\BasilParser\DataStructure\Step as StepData;
use webignition\BasilParser\DataStructure\Test\Configuration as ConfigurationData;
use webignition\BasilParser\DataStructure\Test\Imports as ImportsData;
use webignition\BasilParser\DataStructure\Test\Test as TestData;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Model\ExceptionContext\ExceptionContext;
use webignition\BasilParser\Model\ExceptionContext\ExceptionContextInterface;
use webignition\BasilParser\Tests\Services\FixturePathFinder;

trait UnknownPageElementDataProviderTrait
{
    public function createFromTestDataThrowsUnknownPageElementExceptionDataProvider(): array
    {
        // UnknownPageElementException
        //   thrown when trying to reference a page element not defined within a page
        //
        //   cases:
        //   - test.elements references element that does not exist within a page
        //   - assertion string references element that does not exist within a page
        //   - action string reference element that does not exist within a page
        //
        //   context to be applied in:
        //   - TestFactory calling StepBuilder::build()
        //   - StepFactory calling ActionFactory::createFromActionString()
        //   - StepFactory calling AssertionFactory::createFromAssertionString()

        return [
            'UnknownPageElementException: test.elements references element that does not exist within a page' => [
                'name' => 'test name',
                'testData' => new TestData([
                    TestData::KEY_CONFIGURATION => [
                        ConfigurationData::KEY_BROWSER => 'chrome',
                        ConfigurationData::KEY_URL => 'http://example.com',
                    ],
                    TestData::KEY_IMPORTS => [
                        ImportsData::KEY_PAGES => [
                            'page_import_name' => FixturePathFinder::find('Page/example.com.heading.yml'),
                        ],
                    ],
                    'step name' => [
                        StepData::KEY_ELEMENTS => [
                            'page_import_name.elements.non_existent'
                        ],
                    ],
                ]),
                'expectedException' => UnknownPageElementException::class,
                'expectedExceptionMessage' => 'Unknown page element "non_existent" in page "page_import_name"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                ])
            ],
            'UnknownPageElementException: assertion string references element that does not exist within a page' => [
                'name' => 'test name',
                'testData' => new TestData([
                    TestData::KEY_CONFIGURATION => [
                        ConfigurationData::KEY_BROWSER => 'chrome',
                        ConfigurationData::KEY_URL => 'http://example.com',
                    ],
                    TestData::KEY_IMPORTS => [
                        ImportsData::KEY_PAGES => [
                            'page_import_name' => FixturePathFinder::find('Page/example.com.heading.yml'),
                        ],
                    ],
                    'step name' => [
                        StepData::KEY_ASSERTIONS => [
                            'page_import_name.elements.non_existent exists'
                        ],
                    ],
                ]),
                'expectedException' => UnknownPageElementException::class,
                'expectedExceptionMessage' => 'Unknown page element "non_existent" in page "page_import_name"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                    ExceptionContextInterface::KEY_CONTENT => 'page_import_name.elements.non_existent exists',
                ])
            ],
            'UnknownPageElementException: action string references element that does not exist within a page' => [
                'name' => 'test name',
                'testData' => new TestData([
                    TestData::KEY_CONFIGURATION => [
                        ConfigurationData::KEY_BROWSER => 'chrome',
                        ConfigurationData::KEY_URL => 'http://example.com',
                    ],
                    TestData::KEY_IMPORTS => [
                        ImportsData::KEY_PAGES => [
                            'page_import_name' => FixturePathFinder::find('Page/example.com.heading.yml'),
                        ],
                    ],
                    'step name' => [
                        StepData::KEY_ACTIONS => [
                            'click page_import_name.elements.non_existent'
                        ],
                    ],
                ]),
                'expectedException' => UnknownPageElementException::class,
                'expectedExceptionMessage' => 'Unknown page element "non_existent" in page "page_import_name"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                    ExceptionContextInterface::KEY_CONTENT => 'click page_import_name.elements.non_existent',
                ])
            ],
        ];
    }
}
