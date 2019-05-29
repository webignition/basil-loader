<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Factory\Test;

use webignition\BasilParser\Builder\StepBuilder;
use webignition\BasilParser\Exception\ContextAwareExceptionInterface;
use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievableDataProviderException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\NonRetrievableStepException;
use webignition\BasilParser\Exception\UnknownDataProviderException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Factory\StepFactory;
use webignition\BasilParser\Factory\Test\ConfigurationFactory;
use webignition\BasilParser\Factory\Test\TestFactory;
use webignition\BasilParser\Model\Action\ActionTypes;
use webignition\BasilParser\Model\Action\InteractionAction;
use webignition\BasilParser\Model\Assertion\Assertion;
use webignition\BasilParser\Model\Assertion\AssertionComparisons;
use webignition\BasilParser\Model\DataSet\DataSet;
use webignition\BasilParser\Model\ExceptionContext\ExceptionContext;
use webignition\BasilParser\Model\ExceptionContext\ExceptionContextInterface;
use webignition\BasilParser\Model\Identifier\Identifier;
use webignition\BasilParser\Model\Identifier\IdentifierTypes;
use webignition\BasilParser\Model\Step\Step;
use webignition\BasilParser\Model\Test\Configuration;
use webignition\BasilParser\Model\Test\Test;
use webignition\BasilParser\Model\Test\TestInterface;
use webignition\BasilParser\Model\Value\Value;
use webignition\BasilParser\Model\Value\ValueTypes;
use webignition\BasilParser\Tests\Services\FixturePathFinder;
use webignition\BasilParser\Tests\Services\TestFactoryFactory;

class TestFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TestFactory
     */
    private $testFactory;

    private $invalidYamlPath = '';

    protected function setUp(): void
    {
        parent::setUp();

        $this->testFactory = TestFactoryFactory::create();
        $this->invalidYamlPath = FixturePathFinder::find('invalid-yaml.yml');
    }

    /**
     * @dataProvider createFromTestDataDataProvider
     */
    public function testCreateFromTestData(string $name, array $testData, TestInterface $expectedTest)
    {
        $test = $this->testFactory->createFromTestData($name, $testData);

        $this->assertEquals($expectedTest, $test);
    }

    public function createFromTestDataDataProvider(): array
    {
        $configurationData = [
            ConfigurationFactory::KEY_BROWSER => 'chrome',
            ConfigurationFactory::KEY_URL => 'http://example.com',
        ];

        $expectedConfiguration = new Configuration('chrome', 'http://example.com');

        return [
            'empty' => [
                'name' => '',
                'testData' => [],
                'expectedTest' => new Test(
                    '',
                    new Configuration('', ''),
                    []
                ),
            ],
            'configuration only' => [
                'name' => 'configuration only',
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => $configurationData,
                ],
                'expectedTest' => new Test('configuration only', $expectedConfiguration, []),
            ],
            'invalid inline steps only' => [
                'name' => 'invalid inline steps only',
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => $configurationData,
                    'invalid' => [
                        StepFactory::KEY_ACTIONS => true,
                        StepFactory::KEY_ASSERTIONS => [
                            '',
                            false,
                        ],
                    ],
                ],
                'expectedTest' => new Test('invalid inline steps only', $expectedConfiguration, [
                    'invalid' => new Step([], []),
                ]),
            ],
            'inline step, scalar values' => [
                'name' => 'inline step, scalar values',
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => $configurationData,
                    'verify page is open' => [
                        StepFactory::KEY_ASSERTIONS => [
                            '$page.url is "http://example.com"',
                        ],
                    ],
                    'query "example"' => [
                        StepFactory::KEY_ACTIONS => [
                            'click ".form .submit"',
                        ],
                        StepFactory::KEY_ASSERTIONS => [
                            '$page.title is "example - Example Domain"',
                        ],
                    ],
                ],
                'expectedTest' => new Test('inline step, scalar values', $expectedConfiguration, [
                    'verify page is open' => new Step([], [
                        new Assertion(
                            '$page.url is "http://example.com"',
                            new Identifier(
                                IdentifierTypes::PAGE_OBJECT_PARAMETER,
                                '$page.url'
                            ),
                            AssertionComparisons::IS,
                            new Value(
                                ValueTypes::STRING,
                                'http://example.com'
                            )
                        ),
                    ]),
                    'query "example"' => new Step(
                        [
                            new InteractionAction(
                                ActionTypes::CLICK,
                                new Identifier(
                                    IdentifierTypes::CSS_SELECTOR,
                                    '.form .submit'
                                ),
                                '".form .submit"'
                            ),
                        ],
                        [
                            new Assertion(
                                '$page.title is "example - Example Domain"',
                                new Identifier(
                                    IdentifierTypes::PAGE_OBJECT_PARAMETER,
                                    '$page.title'
                                ),
                                AssertionComparisons::IS,
                                new Value(
                                    ValueTypes::STRING,
                                    'example - Example Domain'
                                )
                            ),
                        ]
                    ),
                ]),
            ],
            'inline step, page element references' => [
                'name' => 'inline step, page element references',
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => $configurationData,
                    TestFactory::KEY_IMPORTS => [
                        TestFactory::KEY_IMPORTS_PAGES => [
                            'page_import_name' => FixturePathFinder::find('Page/example.com.button.heading.yml'),
                        ],
                    ],
                    'query "example"' => [
                        StepFactory::KEY_ACTIONS => [
                            'click page_import_name.elements.button',
                        ],
                        StepFactory::KEY_ASSERTIONS => [
                            'page_import_name.elements.heading is "example"',
                        ],
                    ],
                ],
                'expectedTest' => new Test('inline step, page element references', $expectedConfiguration, [
                    'query "example"' => new Step(
                        [
                            new InteractionAction(
                                ActionTypes::CLICK,
                                new Identifier(
                                    IdentifierTypes::CSS_SELECTOR,
                                    '.button',
                                    null,
                                    'button'
                                ),
                                'page_import_name.elements.button'
                            ),
                        ],
                        [
                            new Assertion(
                                'page_import_name.elements.heading is "example"',
                                new Identifier(
                                    IdentifierTypes::CSS_SELECTOR,
                                    '.heading',
                                    null,
                                    'heading'
                                ),
                                AssertionComparisons::IS,
                                new Value(
                                    ValueTypes::STRING,
                                    'example'
                                )
                            ),
                        ]
                    ),
                ]),
            ],
            'invalid page import path, unused' => [
                'name' => 'invalid page import path, unused',
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => $configurationData,
                    TestFactory::KEY_IMPORTS => [
                        TestFactory::KEY_IMPORTS_PAGES => [
                            'invalid' => '../page/file-does-not-exist.yml',
                        ],
                    ],
                ],
                'expectedTest' => new Test('invalid page import path, unused', $expectedConfiguration, []),
            ],
            'invalid step import path, unused' => [
                'name' => 'invalid step import path, unused',
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => $configurationData,
                    TestFactory::KEY_IMPORTS => [
                        TestFactory::KEY_IMPORTS_STEPS => [
                            'invalid' => '../step/file-does-not-exist.yml',
                        ],
                    ],
                ],
                'expectedTest' => new Test('invalid step import path, unused', $expectedConfiguration, []),
            ],
            'invalid data provider import path, unused' => [
                'name' => 'invalid data provider import path, unused',
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => $configurationData,
                    TestFactory::KEY_IMPORTS => [
                        TestFactory::KEY_IMPORTS_DATA_PROVIDERS => [
                            'invalid' => '../data-provider/file-does-not-exist.yml',
                        ],
                    ],
                ],
                'expectedTest' => new Test('invalid data provider import path, unused', $expectedConfiguration, []),
            ],
            'step import, no parameters' => [
                'name' => 'step import, no parameters',
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => $configurationData,
                    TestFactory::KEY_IMPORTS => [
                        TestFactory::KEY_IMPORTS_STEPS => [
                            'step_import_name' => FixturePathFinder::find('Step/no-parameters.yml'),
                        ],
                    ],
                    'step_name' => [
                        StepBuilder::KEY_USE => 'step_import_name',
                    ],
                ],
                'expectedTest' => new Test(
                    'step import, no parameters',
                    $expectedConfiguration,
                    [
                        'step_name' => new Step(
                            [
                                new InteractionAction(
                                    ActionTypes::CLICK,
                                    new Identifier(
                                        IdentifierTypes::CSS_SELECTOR,
                                        '.button'
                                    ),
                                    '".button"'
                                )
                            ],
                            [
                                new Assertion(
                                    '".heading" includes "Hello World"',
                                    new Identifier(
                                        IdentifierTypes::CSS_SELECTOR,
                                        '.heading'
                                    ),
                                    AssertionComparisons::INCLUDES,
                                    new Value(
                                        ValueTypes::STRING,
                                        'Hello World'
                                    )
                                ),
                            ]
                        ),
                    ]
                ),
            ],
            'step import, inline data' => [
                'name' => 'step import, inline data',
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => $configurationData,
                    TestFactory::KEY_IMPORTS => [
                        TestFactory::KEY_IMPORTS_STEPS => [
                            'step_import_name' => FixturePathFinder::find('Step/data-parameters.yml'),
                        ],
                    ],
                    'step_name' => [
                        StepBuilder::KEY_USE => 'step_import_name',
                        StepBuilder::KEY_DATA => [
                            'data_set_1' => new DataSet([
                                'expected_title' => 'Foo',
                            ]),
                        ]
                    ],
                ],
                'expectedTest' => new Test(
                    'step import, inline data',
                    $expectedConfiguration,
                    [
                        'step_name' => (new Step(
                            [
                                new InteractionAction(
                                    ActionTypes::CLICK,
                                    new Identifier(
                                        IdentifierTypes::CSS_SELECTOR,
                                        '.button'
                                    ),
                                    '".button"'
                                )
                            ],
                            [
                                new Assertion(
                                    '".heading" includes $data.expected_title',
                                    new Identifier(
                                        IdentifierTypes::CSS_SELECTOR,
                                        '.heading'
                                    ),
                                    AssertionComparisons::INCLUDES,
                                    new Value(
                                        ValueTypes::DATA_PARAMETER,
                                        '$data.expected_title'
                                    )
                                ),
                            ]
                        ))->withDataSets([
                            'data_set_1' => new DataSet([
                                'expected_title' => 'Foo',
                            ]),
                        ]),
                    ]
                ),
            ],
            'step import, imported data' => [
                'name' => 'step import, imported data',
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => $configurationData,
                    TestFactory::KEY_IMPORTS => [
                        TestFactory::KEY_IMPORTS_STEPS => [
                            'step_import_name' => FixturePathFinder::find('Step/data-parameters.yml'),
                        ],
                        TestFactory::KEY_IMPORTS_DATA_PROVIDERS => [
                            'data_provider_import_name' =>
                                FixturePathFinder::find('DataProvider/expected-title-only.yml')
                        ],
                    ],
                    'step_name' => [
                        StepBuilder::KEY_USE => 'step_import_name',
                        StepBuilder::KEY_DATA => 'data_provider_import_name',
                    ],
                ],
                'expectedTest' => new Test(
                    'step import, imported data',
                    $expectedConfiguration,
                    [
                        'step_name' => (new Step(
                            [
                                new InteractionAction(
                                    ActionTypes::CLICK,
                                    new Identifier(
                                        IdentifierTypes::CSS_SELECTOR,
                                        '.button'
                                    ),
                                    '".button"'
                                )
                            ],
                            [
                                new Assertion(
                                    '".heading" includes $data.expected_title',
                                    new Identifier(
                                        IdentifierTypes::CSS_SELECTOR,
                                        '.heading'
                                    ),
                                    AssertionComparisons::INCLUDES,
                                    new Value(
                                        ValueTypes::DATA_PARAMETER,
                                        '$data.expected_title'
                                    )
                                ),
                            ]
                        ))->withDataSets([
                            0 => new DataSet([
                                'expected_title' => 'Foo',
                            ]),
                            1 => new DataSet([
                                'expected_title' => 'Bar',
                            ]),
                        ]),
                    ]
                ),
            ],
            'step import, uses page imported page elements' => [
                'name' => 'step import, uses page imported page elements',
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => $configurationData,
                    TestFactory::KEY_IMPORTS => [
                        TestFactory::KEY_IMPORTS_STEPS => [
                            'step_import_name' => FixturePathFinder::find('Step/element-parameters.yml'),
                        ],
                        TestFactory::KEY_IMPORTS_PAGES => [
                            'page_import_name' =>
                                FixturePathFinder::find('Page/example.com.heading.yml')
                        ],
                    ],
                    'step_name' => [
                        StepBuilder::KEY_USE => 'step_import_name',
                        StepBuilder::KEY_ELEMENTS => [
                            'heading' => 'page_import_name.elements.heading'
                        ],
                    ],
                ],
                'expectedTest' => new Test(
                    'step import, uses page imported page elements',
                    $expectedConfiguration,
                    [
                        'step_name' => (new Step(
                            [
                                new InteractionAction(
                                    ActionTypes::CLICK,
                                    new Identifier(
                                        IdentifierTypes::CSS_SELECTOR,
                                        '.button'
                                    ),
                                    '".button"'
                                )
                            ],
                            [
                                new Assertion(
                                    '$elements.heading includes "Hello World"',
                                    new Identifier(
                                        IdentifierTypes::ELEMENT_PARAMETER,
                                        '$elements.heading'
                                    ),
                                    AssertionComparisons::INCLUDES,
                                    new Value(
                                        ValueTypes::STRING,
                                        'Hello World'
                                    )
                                ),
                            ]
                        ))->withElementIdentifiers([
                            'heading' => new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                '.heading',
                                null,
                                'heading'
                            ),
                        ]),
                    ]
                ),
            ],
        ];
    }

    /**
     * @dataProvider createFromTestDataThrowsMalformedPageElementReferenceExceptionDataProvider
     * @dataProvider createFromTestDataThrowsNonRetrievableDataProviderExceptionDataProvider
     * @dataProvider createFromTestDataThrowsNonRetrievablePageExceptionDataProvider
     * @dataProvider createFromTestDataThrowsNonRetrievableStepExceptionDataProvider
     * @dataProvider createFromTestDataThrowsUnknownDataProviderExceptionDataProvider
     * @dataProvider createFromTestDataThrowsUnknownPageElementExceptionDataProvider
     * @dataProvider createFromTestDataThrowsUnknownPageExceptionDataProvider
     */
    public function testCreateFromTestDataThrowsException(
        string $name,
        array $testData,
        string $expectedException,
        string $expectedExceptionMessage,
        ExceptionContext $expectedExceptionContext
    ) {
        try {
            $this->testFactory->createFromTestData($name, $testData);
        } catch (ContextAwareExceptionInterface $contextAwareException) {
            $this->assertInstanceOf($expectedException, $contextAwareException);
            $this->assertEquals($expectedExceptionMessage, $contextAwareException->getMessage());
            $this->assertEquals($expectedExceptionContext, $contextAwareException->getExceptionContext());
        }
    }

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
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => [
                        ConfigurationFactory::KEY_BROWSER => 'chrome',
                        ConfigurationFactory::KEY_URL => 'http://example.com',
                    ],
                    'step name' => [
                        StepFactory::KEY_ASSERTIONS => [
                            'malformed_reference is "assertion one value"',
                        ],
                    ],
                ],
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
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => [
                        ConfigurationFactory::KEY_BROWSER => 'chrome',
                        ConfigurationFactory::KEY_URL => 'http://example.com',
                    ],
                    'step name' => [
                        StepFactory::KEY_ASSERTIONS => [
                            '".heading" is "assertion one value"',
                            'malformed_reference is "assertion two value"',
                        ],
                    ],
                ],
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
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => [
                        ConfigurationFactory::KEY_BROWSER => 'chrome',
                        ConfigurationFactory::KEY_URL => 'http://example.com',
                    ],
                    'step name' => [
                        StepFactory::KEY_ACTIONS => [
                            'click action_one_element_reference',
                        ],
                    ],
                ],
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
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => [
                        ConfigurationFactory::KEY_BROWSER => 'chrome',
                        ConfigurationFactory::KEY_URL => 'http://example.com',
                    ],
                    'step name' => [
                        StepFactory::KEY_ACTIONS => [
                            'click ".heading"',
                            'click action_two_element_reference',
                        ],
                    ],
                ],
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
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => [
                        ConfigurationFactory::KEY_BROWSER => 'chrome',
                        ConfigurationFactory::KEY_URL => 'http://example.com',
                    ],
                    TestFactory::KEY_IMPORTS => [
                        TestFactory::KEY_IMPORTS_STEPS => [
                            'step_import_name' => FixturePathFinder::find('Step/element-parameters.yml'),
                        ],
                        TestFactory::KEY_IMPORTS_PAGES => [],
                    ],
                    'step one' => [
                        StepBuilder::KEY_USE => 'step_import_name',
                        StepBuilder::KEY_ELEMENTS => [
                            'heading' => 'invalid_page_element_reference',
                        ],
                    ],
                ],
                'expectedException' => MalformedPageElementReferenceException::class,
                'expectedExceptionMessage' => 'Malformed page element reference "invalid_page_element_reference"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step one',
                ]),
            ],
            'MalformedPageElementReferenceException: test.elements contains malformed reference (2)' => [
                'name' => 'test name',
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => [
                        ConfigurationFactory::KEY_BROWSER => 'chrome',
                        ConfigurationFactory::KEY_URL => 'http://example.com',
                    ],
                    TestFactory::KEY_IMPORTS => [
                        TestFactory::KEY_IMPORTS_STEPS => [
                            'step_import_name' => FixturePathFinder::find('Step/element-parameters.yml'),
                        ],
                        TestFactory::KEY_IMPORTS_PAGES => [],
                    ],
                    'step one' => [
                        StepFactory::KEY_ASSERTIONS => [
                            '$page.url is "http://example.com"',
                        ],
                    ],
                    'step two' => [
                        StepBuilder::KEY_USE => 'step_import_name',
                        StepBuilder::KEY_ELEMENTS => [
                            'heading' => 'malformed_page_element_reference',
                        ],
                    ],
                ],
                'expectedException' => MalformedPageElementReferenceException::class,
                'expectedExceptionMessage' => 'Malformed page element reference "malformed_page_element_reference"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step two',
                ])
            ],
        ];
    }

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
                        StepFactory::KEY_ASSERTIONS => [
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
                        StepFactory::KEY_ASSERTIONS => [
                            '".header" exists',
                        ],
                    ],
                    'step two' => [
                        StepFactory::KEY_ASSERTIONS => [
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
                        StepFactory::KEY_ASSERTIONS => [
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
                        StepFactory::KEY_ASSERTIONS => [
                            '".header" exists',
                        ],
                    ],
                    'step two' => [
                        StepFactory::KEY_ASSERTIONS => [
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
                        StepFactory::KEY_ACTIONS => [
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
                        StepFactory::KEY_ACTIONS => [
                            'click ".heading"',
                        ],
                    ],
                    'step two' => [
                        StepFactory::KEY_ACTIONS => [
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
                        StepFactory::KEY_ACTIONS => [
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
                        StepFactory::KEY_ACTIONS => [
                            'click ".header"'
                        ],
                    ],
                    'step two' => [
                        StepFactory::KEY_ACTIONS => [
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
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => [
                        ConfigurationFactory::KEY_BROWSER => 'chrome',
                        ConfigurationFactory::KEY_URL => 'http://example.com',
                    ],
                    TestFactory::KEY_IMPORTS => [
                        TestFactory::KEY_IMPORTS_PAGES => [
                            'page_import_name' => FixturePathFinder::find('Page/example.com.heading.yml'),
                        ],
                    ],
                    'step name' => [
                        StepBuilder::KEY_ELEMENTS => [
                            'page_import_name.elements.non_existent'
                        ],
                    ],
                ],
                'expectedException' => UnknownPageElementException::class,
                'expectedExceptionMessage' => 'Unknown page element "non_existent" in page "page_import_name"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                ])
            ],
            'UnknownPageElementException: assertion string references element that does not exist within a page' => [
                'name' => 'test name',
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => [
                        ConfigurationFactory::KEY_BROWSER => 'chrome',
                        ConfigurationFactory::KEY_URL => 'http://example.com',
                    ],
                    TestFactory::KEY_IMPORTS => [
                        TestFactory::KEY_IMPORTS_PAGES => [
                            'page_import_name' => FixturePathFinder::find('Page/example.com.heading.yml'),
                        ],
                    ],
                    'step name' => [
                        StepFactory::KEY_ASSERTIONS => [
                            'page_import_name.elements.non_existent exists'
                        ],
                    ],
                ],
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
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => [
                        ConfigurationFactory::KEY_BROWSER => 'chrome',
                        ConfigurationFactory::KEY_URL => 'http://example.com',
                    ],
                    TestFactory::KEY_IMPORTS => [
                        TestFactory::KEY_IMPORTS_PAGES => [
                            'page_import_name' => FixturePathFinder::find('Page/example.com.heading.yml'),
                        ],
                    ],
                    'step name' => [
                        StepFactory::KEY_ACTIONS => [
                            'click page_import_name.elements.non_existent'
                        ],
                    ],
                ],
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

    public function createFromTestDataThrowsExceptionDataProvider(): array
    {
        // UnknownStepException
        //   thrown when trying to reference a step not defined within a collection
        //
        //   cases:
        //   - step.use references step not defined within a collection
        //
        //   context to be applied in:
        //   - TestFactory calling StepBuilder::build()

        return [];
    }
}
