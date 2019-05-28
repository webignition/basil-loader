<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Factory\Test;

use webignition\BasilParser\Factory\StepFactory;
use webignition\BasilParser\Factory\Test\ConfigurationFactory;
use webignition\BasilParser\Factory\Test\TestFactory;
use webignition\BasilParser\Model\Action\ActionTypes;
use webignition\BasilParser\Model\Action\InteractionAction;
use webignition\BasilParser\Model\Assertion\Assertion;
use webignition\BasilParser\Model\Assertion\AssertionComparisons;
use webignition\BasilParser\Model\DataSet\DataSet;
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->testFactory = TestFactoryFactory::create();
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
                        'use' => 'step_import_name',
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
                        'use' => 'step_import_name',
                        'data' => [
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
                        'use' => 'step_import_name',
                        'data' => 'data_provider_import_name',
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
                        'use' => 'step_import_name',
                        'elements' => [
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
}
