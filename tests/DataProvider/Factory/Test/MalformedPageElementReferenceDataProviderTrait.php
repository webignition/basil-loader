<?php

namespace webignition\BasilParser\Tests\DataProvider\Factory\Test;

use webignition\BasilContextAwareException\ExceptionContext\ExceptionContext;
use webignition\BasilContextAwareException\ExceptionContext\ExceptionContextInterface;
use webignition\BasilDataStructure\PathResolver;
use webignition\BasilDataStructure\Step as StepData;
use webignition\BasilDataStructure\Test\Configuration as ConfigurationData;
use webignition\BasilDataStructure\Test\Imports as ImportsData;
use webignition\BasilDataStructure\Test\Test as TestData;
use webignition\BasilModelFactory\MalformedPageElementReferenceException;
use webignition\BasilParser\Tests\Services\FixturePathFinder;

trait MalformedPageElementReferenceDataProviderTrait
{
    public function createFromTestDataThrowsMalformedPageElementReferenceExceptionDataProvider(): array
    {
        // MalformedPageElementReferenceException
        //   thrown when trying to uses a page element reference that is not of the correct form
        //
        //   cases:
        //   - assertion string contains malformed reference
        //   - action string contains malformed reference
        //   - test.elements contains malformed reference

        return [
            'MalformedPageElementReferenceException: assertion string contains malformed reference (1)' => [
                'name' => 'test name',
                'testData' => new TestData(
                    PathResolver::create(),
                    [
                        TestData::KEY_CONFIGURATION => [
                            ConfigurationData::KEY_BROWSER => 'chrome',
                            ConfigurationData::KEY_URL => 'http://example.com',
                        ],
                        'step name' => [
                            StepData::KEY_ASSERTIONS => [
                                'malformed_reference is "assertion one value"',
                            ],
                        ],
                    ]
                ),
                'expectedException' => MalformedPageElementReferenceException::class,
                'expectedExceptionMessage' => 'Malformed page element reference "malformed_reference"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                    ExceptionContextInterface::KEY_CONTENT => 'malformed_reference is "assertion one value"',
                ]),
            ],
            'MalformedPageElementReferenceException: assertion string contains malformed reference (2)' => [
                'name' => 'test name',
                'testData' => new TestData(
                    PathResolver::create(),
                    [
                        TestData::KEY_CONFIGURATION => [
                            ConfigurationData::KEY_BROWSER => 'chrome',
                            ConfigurationData::KEY_URL => 'http://example.com',
                        ],
                        'step name' => [
                            StepData::KEY_ASSERTIONS => [
                                '".heading" is "assertion one value"',
                                'malformed_reference is "assertion two value"',
                            ],
                        ],
                    ]
                ),
                'expectedException' => MalformedPageElementReferenceException::class,
                'expectedExceptionMessage' => 'Malformed page element reference "malformed_reference"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                    ExceptionContextInterface::KEY_CONTENT => 'malformed_reference is "assertion two value"',
                ]),
            ],
            'MalformedPageElementReferenceException: action string contains malformed reference (1)' => [
                'name' => 'test name',
                'testData' => new TestData(
                    PathResolver::create(),
                    [
                        TestData::KEY_CONFIGURATION => [
                            ConfigurationData::KEY_BROWSER => 'chrome',
                            ConfigurationData::KEY_URL => 'http://example.com',
                        ],
                        'step name' => [
                            StepData::KEY_ACTIONS => [
                                'click action_one_element_reference',
                            ],
                        ],
                    ]
                ),
                'expectedException' => MalformedPageElementReferenceException::class,
                'expectedExceptionMessage' => 'Malformed page element reference "action_one_element_reference"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                    ExceptionContextInterface::KEY_CONTENT => 'click action_one_element_reference',
                ]),
            ],
            'MalformedPageElementReferenceException: action string contains malformed reference (2)' => [
                'name' => 'test name',
                'testData' => new TestData(
                    PathResolver::create(),
                    [
                        TestData::KEY_CONFIGURATION => [
                            ConfigurationData::KEY_BROWSER => 'chrome',
                            ConfigurationData::KEY_URL => 'http://example.com',
                        ],
                        'step name' => [
                            StepData::KEY_ACTIONS => [
                                'click ".heading"',
                                'click action_two_element_reference',
                            ],
                        ],
                    ]
                ),
                'expectedException' => MalformedPageElementReferenceException::class,
                'expectedExceptionMessage' => 'Malformed page element reference "action_two_element_reference"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                    ExceptionContextInterface::KEY_CONTENT => 'click action_two_element_reference',
                ]),
            ],
            'MalformedPageElementReferenceException: test.elements contains malformed reference (1)' => [
                'name' => 'test name',
                'testData' => new TestData(
                    PathResolver::create(),
                    [
                        TestData::KEY_CONFIGURATION => [
                            ConfigurationData::KEY_BROWSER => 'chrome',
                            ConfigurationData::KEY_URL => 'http://example.com',
                        ],
                        TestData::KEY_IMPORTS => [
                            ImportsData::KEY_STEPS => [
                                'step_import_name' => FixturePathFinder::find('Step/element-parameters.yml'),
                            ],
                            ImportsData::KEY_PAGES => [],
                        ],
                        'step one' => [
                            StepData::KEY_USE => 'step_import_name',
                            StepData::KEY_ELEMENTS => [
                                'heading' => 'invalid_page_element_reference',
                            ],
                        ],
                    ]
                ),
                'expectedException' => MalformedPageElementReferenceException::class,
                'expectedExceptionMessage' => 'Malformed page element reference "invalid_page_element_reference"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step one',
                ]),
            ],
            'MalformedPageElementReferenceException: test.elements contains malformed reference (2)' => [
                'name' => 'test name',
                'testData' => new TestData(
                    PathResolver::create(),
                    [
                        TestData::KEY_CONFIGURATION => [
                            ConfigurationData::KEY_BROWSER => 'chrome',
                            ConfigurationData::KEY_URL => 'http://example.com',
                        ],
                        TestData::KEY_IMPORTS => [
                            ImportsData::KEY_STEPS => [
                                'step_import_name' => FixturePathFinder::find('Step/element-parameters.yml'),
                            ],
                            ImportsData::KEY_PAGES => [],
                        ],
                        'step one' => [
                            StepData::KEY_ASSERTIONS => [
                                '$page.url is "http://example.com"',
                            ],
                        ],
                        'step two' => [
                            StepData::KEY_USE => 'step_import_name',
                            StepData::KEY_ELEMENTS => [
                                'heading' => 'malformed_page_element_reference',
                            ],
                        ],
                    ]
                ),
                'expectedException' => MalformedPageElementReferenceException::class,
                'expectedExceptionMessage' => 'Malformed page element reference "malformed_page_element_reference"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step two',
                ])
            ],
        ];
    }
}
