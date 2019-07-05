<?php

namespace webignition\BasilParser\Tests\DataProvider\Factory\Test;

use webignition\BasilModel\Action\ActionTypes;
use webignition\BasilModel\Action\InteractionAction;
use webignition\BasilModel\Assertion\Assertion;
use webignition\BasilModel\Assertion\AssertionComparisons;
use webignition\BasilModel\DataSet\DataSet;
use webignition\BasilModel\Identifier\Identifier;
use webignition\BasilModel\Identifier\IdentifierTypes;
use webignition\BasilModel\Step\PendingImportResolutionStep;
use webignition\BasilModel\Step\Step;
use webignition\BasilModel\Test\Configuration;
use webignition\BasilModel\Test\Test;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\Value;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilParser\DataStructure\Step as StepData;
use webignition\BasilParser\DataStructure\Test\Configuration as ConfigurationData;
use webignition\BasilParser\DataStructure\Test\Imports as ImportsData;
use webignition\BasilParser\DataStructure\Test\Test as TestData;
use webignition\BasilParser\Tests\Services\FixturePathFinder;
use webignition\BasilParser\Tests\Services\PathResolverFactory;

trait CreateFromTestDataDataProviderTrait
{
    public function createFromTestDataDataProvider(): array
    {
        $configurationData = [
            ConfigurationData::KEY_BROWSER => 'chrome',
            ConfigurationData::KEY_URL => 'http://example.com',
        ];

        $expectedConfiguration = new Configuration('chrome', 'http://example.com');

        return [
            'empty' => [
                'name' => '',
                'testData' => new TestData(PathResolverFactory::create(), []),
                'expectedTest' => new Test(
                    '',
                    new Configuration('', ''),
                    []
                ),
            ],
            'configuration only' => [
                'name' => 'configuration only',
                'testData' => new TestData(
                    PathResolverFactory::create(),
                    [
                        TestData::KEY_CONFIGURATION => $configurationData,
                    ]
                ),
                'expectedTest' => new Test('configuration only', $expectedConfiguration, []),
            ],
            'invalid inline steps only' => [
                'name' => 'invalid inline steps only',
                'testData' => new TestData(
                    PathResolverFactory::create(),
                    [
                        TestData::KEY_CONFIGURATION => $configurationData,
                        'invalid' => [
                            StepData::KEY_ACTIONS => true,
                            StepData::KEY_ASSERTIONS => [
                                '',
                                false,
                            ],
                        ],
                    ]
                ),
                'expectedTest' => new Test('invalid inline steps only', $expectedConfiguration, [
                    'invalid' => new Step([], []),
                ]),
            ],
            'inline step, scalar values' => [
                'name' => 'inline step, scalar values',
                'testData' => new TestData(
                    PathResolverFactory::create(),
                    [
                        TestData::KEY_CONFIGURATION => $configurationData,
                        'verify page is open' => [
                            StepData::KEY_ASSERTIONS => [
                                '$page.url is "http://example.com"',
                            ],
                        ],
                        'query "example"' => [
                            StepData::KEY_ACTIONS => [
                                'click ".form .submit"',
                            ],
                            StepData::KEY_ASSERTIONS => [
                                '$page.title is "example - Example Domain"',
                            ],
                        ],
                    ]
                ),
                'expectedTest' => new Test('inline step, scalar values', $expectedConfiguration, [
                    'verify page is open' => new Step([], [
                        new Assertion(
                            '$page.url is "http://example.com"',
                            new Identifier(
                                IdentifierTypes::PAGE_OBJECT_PARAMETER,
                                new ObjectValue(
                                    ValueTypes::PAGE_OBJECT_PROPERTY,
                                    '$page.url',
                                    'page',
                                    'url'
                                )
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
                                    new Value(
                                        ValueTypes::STRING,
                                        '.form .submit'
                                    )
                                ),
                                '".form .submit"'
                            ),
                        ],
                        [
                            new Assertion(
                                '$page.title is "example - Example Domain"',
                                new Identifier(
                                    IdentifierTypes::PAGE_OBJECT_PARAMETER,
                                    new ObjectValue(
                                        ValueTypes::PAGE_OBJECT_PROPERTY,
                                        '$page.title',
                                        'page',
                                        'title'
                                    )
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
                'testData' => new TestData(
                    PathResolverFactory::create(),
                    [
                        TestData::KEY_CONFIGURATION => $configurationData,
                        TestData::KEY_IMPORTS => [
                            ImportsData::KEY_PAGES => [
                                'page_import_name' => FixturePathFinder::find('Page/example.com.button.heading.yml'),
                            ],
                        ],
                        'query "example"' => [
                            StepData::KEY_ACTIONS => [
                                'click page_import_name.elements.button',
                            ],
                            StepData::KEY_ASSERTIONS => [
                                'page_import_name.elements.heading is "example"',
                            ],
                        ],
                    ]
                ),
                'expectedTest' => new Test('inline step, page element references', $expectedConfiguration, [
                    'query "example"' => new Step(
                        [
                            new InteractionAction(
                                ActionTypes::CLICK,
                                new Identifier(
                                    IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                                    new Value(
                                        ValueTypes::STRING,
                                        'page_import_name.elements.button'
                                    ),
                                    null
                                ),
                                'page_import_name.elements.button'
                            ),
                        ],
                        [
                            new Assertion(
                                'page_import_name.elements.heading is "example"',
                                new Identifier(
                                    IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                                    new Value(
                                        ValueTypes::STRING,
                                        'page_import_name.elements.heading'
                                    ),
                                    null
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
                'testData' => new TestData(
                    PathResolverFactory::create(),
                    [
                        TestData::KEY_CONFIGURATION => $configurationData,
                        TestData::KEY_IMPORTS => [
                            ImportsData::KEY_PAGES => [
                                'invalid' => '../page/file-does-not-exist.yml',
                            ],
                        ],
                    ]
                ),
                'expectedTest' => new Test('invalid page import path, unused', $expectedConfiguration, []),
            ],
            'invalid step import path, unused' => [
                'name' => 'invalid step import path, unused',
                'testData' => new TestData(
                    PathResolverFactory::create(),
                    [
                        TestData::KEY_CONFIGURATION => $configurationData,
                        TestData::KEY_IMPORTS => [
                            ImportsData::KEY_STEPS => [
                                'invalid' => '../step/file-does-not-exist.yml',
                            ],
                        ],
                    ]
                ),
                'expectedTest' => new Test('invalid step import path, unused', $expectedConfiguration, []),
            ],
            'invalid data provider import path, unused' => [
                'name' => 'invalid data provider import path, unused',
                'testData' => new TestData(
                    PathResolverFactory::create(),
                    [
                        TestData::KEY_CONFIGURATION => $configurationData,
                        TestData::KEY_IMPORTS => [
                            ImportsData::KEY_DATA_PROVIDERS => [
                                'invalid' => '../data-provider/file-does-not-exist.yml',
                            ],
                        ],
                    ]
                ),
                'expectedTest' => new Test('invalid data provider import path, unused', $expectedConfiguration, []),
            ],
            'step import, no parameters' => [
                'name' => 'step import, no parameters',
                'testData' => new TestData(
                    PathResolverFactory::create(),
                    [
                        TestData::KEY_CONFIGURATION => $configurationData,
                        TestData::KEY_IMPORTS => [
                            ImportsData::KEY_STEPS => [
                                'step_import_name' => FixturePathFinder::find('Step/no-parameters.yml'),
                            ],
                        ],
                        'step_name' => [
                            StepData::KEY_USE => 'step_import_name',
                        ],
                    ]
                ),
                'expectedTest' => new Test(
                    'step import, no parameters',
                    $expectedConfiguration,
                    [
                        'step_name' => new PendingImportResolutionStep([], [], 'step_import_name', ''),
                    ]
                ),
            ],
            'step import, inline data' => [
                'name' => 'step import, inline data',
                'testData' => new TestData(
                    PathResolverFactory::create(),
                    [
                        TestData::KEY_CONFIGURATION => $configurationData,
                        TestData::KEY_IMPORTS => [
                            ImportsData::KEY_STEPS => [
                                'step_import_name' => FixturePathFinder::find('Step/data-parameters.yml'),
                            ],
                        ],
                        'step_name' => [
                            StepData::KEY_USE => 'step_import_name',
                            StepData::KEY_DATA => [
                                'data_set_1' => [
                                    'expected_title' => 'Foo',
                                ],
                            ]
                        ],
                    ]
                ),
                'expectedTest' => new Test(
                    'step import, inline data',
                    $expectedConfiguration,
                    [
                        'step_name' => (new PendingImportResolutionStep(
                            [],
                            [],
                            'step_import_name',
                            ''
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
                'testData' => new TestData(
                    PathResolverFactory::create(),
                    [
                        TestData::KEY_CONFIGURATION => $configurationData,
                        TestData::KEY_IMPORTS => [
                            ImportsData::KEY_STEPS => [
                                'step_import_name' => FixturePathFinder::find('Step/data-parameters.yml'),
                            ],
                            ImportsData::KEY_DATA_PROVIDERS => [
                                'data_provider_import_name' =>
                                    FixturePathFinder::find('DataProvider/expected-title-only.yml')
                            ],
                        ],
                        'step_name' => [
                            StepData::KEY_USE => 'step_import_name',
                            StepData::KEY_DATA => 'data_provider_import_name',
                        ],
                    ]
                ),
                'expectedTest' => new Test(
                    'step import, imported data',
                    $expectedConfiguration,
                    [
                        'step_name' => new PendingImportResolutionStep(
                            [],
                            [],
                            'step_import_name',
                            'data_provider_import_name'
                        ),
                    ]
                ),
            ],
            'step import, uses page imported page elements' => [
                'name' => 'step import, uses page imported page elements',
                'testData' => new TestData(
                    PathResolverFactory::create(),
                    [
                        TestData::KEY_CONFIGURATION => $configurationData,
                        TestData::KEY_IMPORTS => [
                            ImportsData::KEY_STEPS => [
                                'step_import_name' => FixturePathFinder::find('Step/element-parameters.yml'),
                            ],
                            ImportsData::KEY_PAGES => [
                                'page_import_name' =>
                                    FixturePathFinder::find('Page/example.com.heading.yml')
                            ],
                        ],
                        'step_name' => [
                            StepData::KEY_USE => 'step_import_name',
                            StepData::KEY_ELEMENTS => [
                                'heading' => 'page_import_name.elements.heading'
                            ],
                        ],
                    ]
                ),
                'expectedTest' => new Test(
                    'step import, uses page imported page elements',
                    $expectedConfiguration,
                    [
                        'step_name' => (new PendingImportResolutionStep(
                            [],
                            [],
                            'step_import_name',
                            ''
                        ))->withElementIdentifiers([
                            new Identifier(
                                IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                                new Value(
                                    ValueTypes::STRING,
                                    'page_import_name.elements.heading'
                                ),
                                null,
                                'heading'
                            )
                        ]),
                    ]
                ),
            ],
        ];
    }
}
