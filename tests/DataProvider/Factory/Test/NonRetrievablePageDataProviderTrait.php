<?php

namespace webignition\BasilParser\Tests\DataProvider\Factory\Test;

use webignition\BasilParser\DataStructure\Step as StepData;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Factory\StepFactory;
use webignition\BasilParser\Factory\Test\ConfigurationFactory;
use webignition\BasilParser\Factory\Test\TestFactory;
use webignition\BasilParser\Model\ExceptionContext\ExceptionContext;
use webignition\BasilParser\Model\ExceptionContext\ExceptionContextInterface;

trait NonRetrievablePageDataProviderTrait
{
    public function createFromTestDataThrowsNonRetrievablePageExceptionDataProvider(): array
    {
        // NonRetrievablePageException
        //   thrown when trying to load a page that does not exist or which is not valid yaml
        //
        //   cases:
        //   - config.url references page that does not exist
        //   - config.url references page that contains invalid yaml
        //   - assertion string references page that does not exist
        //   - assertion string references page that contains invalid yaml
        //   - action string reference page that does not exist
        //   - action string references page that contains invalid yaml
        //
        //   context to be applied in:
        //   - TestFactory calling ConfigurationFactory::createFromConfigurationData()
        //   - TestFactory calling StepBuilder::build()
        //   - StepFactory calling ActionFactory::createFromActionString()
        //   - StepFactory calling AssertionFactory::createFromAssertionString()

        return [
            'NonRetrievablePageException: config.url references page that does not exist' => [
                'name' => 'test name',
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => [
                        ConfigurationFactory::KEY_BROWSER => 'chrome',
                        ConfigurationFactory::KEY_URL => 'page_import_name.url',
                    ],
                    TestFactory::KEY_IMPORTS => [
                        TestFactory::KEY_IMPORTS_PAGES => [
                            'page_import_name' => 'Page/non-existent.yml',
                        ],
                    ],
                ],
                'expectedException' => NonRetrievablePageException::class,
                'expectedExceptionMessage' => 'Cannot retrieve page "page_import_name" from "Page/non-existent.yml"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                ])
            ],
            'NonRetrievablePageException: config.url references page that contains invalid yaml' => [
                'name' => 'test name',
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => [
                        ConfigurationFactory::KEY_BROWSER => 'chrome',
                        ConfigurationFactory::KEY_URL => 'page_import_name.url',
                    ],
                    TestFactory::KEY_IMPORTS => [
                        TestFactory::KEY_IMPORTS_PAGES => [
                            'page_import_name' => $this->invalidYamlPath,
                        ],
                    ],
                ],
                'expectedException' => NonRetrievablePageException::class,
                'expectedExceptionMessage' =>
                    'Cannot retrieve page "page_import_name" from "' . $this->invalidYamlPath . '"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                ])
            ],
            'NonRetrievablePageException: assertion string references page that does not exist (1)' => [
                'name' => 'test name',
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => [
                        ConfigurationFactory::KEY_BROWSER => 'chrome',
                        ConfigurationFactory::KEY_URL => 'http://example.com',
                    ],
                    TestFactory::KEY_IMPORTS => [
                        TestFactory::KEY_IMPORTS_PAGES => [
                            'page_import_name' => 'Page/non-existent.yml',
                        ],
                    ],
                    'step one' => [
                        StepData::KEY_ASSERTIONS => [
                            'page_import_name.elements.element_name exists',
                        ],
                    ],
                ],
                'expectedException' => NonRetrievablePageException::class,
                'expectedExceptionMessage' => 'Cannot retrieve page "page_import_name" from "Page/non-existent.yml"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step one',
                    ExceptionContextInterface::KEY_CONTENT => 'page_import_name.elements.element_name exists',
                ])
            ],
            'NonRetrievablePageException: assertion string references page that does not exist (2)' => [
                'name' => 'test name',
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => [
                        ConfigurationFactory::KEY_BROWSER => 'chrome',
                        ConfigurationFactory::KEY_URL => 'http://example.com',
                    ],
                    TestFactory::KEY_IMPORTS => [
                        TestFactory::KEY_IMPORTS_PAGES => [
                            'page_import_name' => 'Page/non-existent.yml',
                        ],
                    ],
                    'step one' => [
                        StepData::KEY_ASSERTIONS => [
                            '".header" exists',
                        ],
                    ],
                    'step two' => [
                        StepData::KEY_ASSERTIONS => [
                            'page_import_name.elements.element_name exists',
                        ],
                    ],
                ],
                'expectedException' => NonRetrievablePageException::class,
                'expectedExceptionMessage' => 'Cannot retrieve page "page_import_name" from "Page/non-existent.yml"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step two',
                    ExceptionContextInterface::KEY_CONTENT => 'page_import_name.elements.element_name exists',
                ])
            ],
            'NonRetrievablePageException: assertion string references page that contains invalid yaml (1)' => [
                'name' => 'test name',
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => [
                        ConfigurationFactory::KEY_BROWSER => 'chrome',
                        ConfigurationFactory::KEY_URL => 'http://example.com',
                    ],
                    TestFactory::KEY_IMPORTS => [
                        TestFactory::KEY_IMPORTS_PAGES => [
                            'page_import_name' => $this->invalidYamlPath,
                        ],
                    ],
                    'step one' => [
                        StepData::KEY_ASSERTIONS => [
                            'page_import_name.elements.element_name exists',
                        ],
                    ],
                ],
                'expectedException' => NonRetrievablePageException::class,
                'expectedExceptionMessage' =>
                    'Cannot retrieve page "page_import_name" from "' . $this->invalidYamlPath . '"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step one',
                    ExceptionContextInterface::KEY_CONTENT => 'page_import_name.elements.element_name exists',
                ])
            ],
            'NonRetrievablePageException: assertion string references page that contains invalid yaml (2)' => [
                'name' => 'test name',
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => [
                        ConfigurationFactory::KEY_BROWSER => 'chrome',
                        ConfigurationFactory::KEY_URL => 'http://example.com',
                    ],
                    TestFactory::KEY_IMPORTS => [
                        TestFactory::KEY_IMPORTS_PAGES => [
                            'page_import_name' => $this->invalidYamlPath,
                        ],
                    ],
                    'step one' => [
                        StepData::KEY_ASSERTIONS => [
                            '".header" exists',
                        ],
                    ],
                    'step two' => [
                        StepData::KEY_ASSERTIONS => [
                            'page_import_name.elements.element_name exists'
                        ],
                    ],
                ],
                'expectedException' => NonRetrievablePageException::class,
                'expectedExceptionMessage' =>
                    'Cannot retrieve page "page_import_name" from "' . $this->invalidYamlPath . '"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step two',
                    ExceptionContextInterface::KEY_CONTENT => 'page_import_name.elements.element_name exists',
                ])
            ],
            'NonRetrievablePageException: action string references page that does not exist (1)' => [
                'name' => 'test name',
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => [
                        ConfigurationFactory::KEY_BROWSER => 'chrome',
                        ConfigurationFactory::KEY_URL => 'http://example.com',
                    ],
                    TestFactory::KEY_IMPORTS => [
                        TestFactory::KEY_IMPORTS_PAGES => [
                            'page_import_name' => 'Page/non-existent.yml',
                        ],
                    ],
                    'step one' => [
                        StepData::KEY_ACTIONS => [
                            'click page_import_name.elements.element_name',
                        ],
                    ],
                ],
                'expectedException' => NonRetrievablePageException::class,
                'expectedExceptionMessage' => 'Cannot retrieve page "page_import_name" from "Page/non-existent.yml"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step one',
                    ExceptionContextInterface::KEY_CONTENT => 'click page_import_name.elements.element_name',
                ])
            ],
            'NonRetrievablePageException: action string references page that does not exist (2)' => [
                'name' => 'test name',
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => [
                        ConfigurationFactory::KEY_BROWSER => 'chrome',
                        ConfigurationFactory::KEY_URL => 'http://example.com',
                    ],
                    TestFactory::KEY_IMPORTS => [
                        TestFactory::KEY_IMPORTS_PAGES => [
                            'page_import_name' => 'Page/non-existent.yml',
                        ],
                    ],
                    'step one' => [
                        StepData::KEY_ACTIONS => [
                            'click ".heading"',
                        ],
                    ],
                    'step two' => [
                        StepData::KEY_ACTIONS => [
                            'click page_import_name.elements.element_name',
                        ],
                    ],
                ],
                'expectedException' => NonRetrievablePageException::class,
                'expectedExceptionMessage' => 'Cannot retrieve page "page_import_name" from "Page/non-existent.yml"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step two',
                    ExceptionContextInterface::KEY_CONTENT => 'click page_import_name.elements.element_name',
                ])
            ],
            'NonRetrievablePageException: action string references page that contains invalid yaml (1)' => [
                'name' => 'test name',
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => [
                        ConfigurationFactory::KEY_BROWSER => 'chrome',
                        ConfigurationFactory::KEY_URL => 'http://example.com',
                    ],
                    TestFactory::KEY_IMPORTS => [
                        TestFactory::KEY_IMPORTS_PAGES => [
                            'page_import_name' => $this->invalidYamlPath,
                        ],
                    ],
                    'step one' => [
                        StepData::KEY_ACTIONS => [
                            'click page_import_name.elements.element_name'
                        ],
                    ],
                ],
                'expectedException' => NonRetrievablePageException::class,
                'expectedExceptionMessage' =>
                    'Cannot retrieve page "page_import_name" from "' . $this->invalidYamlPath . '"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step one',
                    ExceptionContextInterface::KEY_CONTENT => 'click page_import_name.elements.element_name',
                ])
            ],
            'NonRetrievablePageException: action string references page that contains invalid yaml (2)' => [
                'name' => 'test name',
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => [
                        ConfigurationFactory::KEY_BROWSER => 'chrome',
                        ConfigurationFactory::KEY_URL => 'http://example.com',
                    ],
                    TestFactory::KEY_IMPORTS => [
                        TestFactory::KEY_IMPORTS_PAGES => [
                            'page_import_name' => $this->invalidYamlPath,
                        ],
                    ],
                    'step one' => [
                        StepData::KEY_ACTIONS => [
                            'click ".header"'
                        ],
                    ],
                    'step two' => [
                        StepData::KEY_ACTIONS => [
                            'click page_import_name.elements.element_name'
                        ],
                    ],
                ],
                'expectedException' => NonRetrievablePageException::class,
                'expectedExceptionMessage' =>
                    'Cannot retrieve page "page_import_name" from "' . $this->invalidYamlPath . '"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step two',
                    ExceptionContextInterface::KEY_CONTENT => 'click page_import_name.elements.element_name',
                ])
            ],
        ];
    }
}
