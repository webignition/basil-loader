<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Builder;

use Nyholm\Psr7\Uri;
use webignition\BasilModel\Action\ActionTypes;
use webignition\BasilModel\Action\InputAction;
use webignition\BasilModel\Action\InteractionAction;
use webignition\BasilModel\Assertion\Assertion;
use webignition\BasilModel\Assertion\AssertionComparisons;
use webignition\BasilModel\DataSet\DataSet;
use webignition\BasilModel\DataSet\DataSetCollection;
use webignition\BasilModel\Identifier\ElementIdentifier;
use webignition\BasilModel\Identifier\IdentifierCollection;
use webignition\BasilModel\Page\Page;
use webignition\BasilModel\Step\Step;
use webignition\BasilModel\Step\StepInterface;
use webignition\BasilModel\Value\ElementValue;
use webignition\BasilModel\Value\EnvironmentValue;
use webignition\BasilModel\Value\LiteralValue;
use webignition\BasilModel\Value\ObjectNames;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilModelFactory\MalformedPageElementReferenceException;
use webignition\BasilParser\Builder\StepBuilder;
use webignition\BasilDataStructure\Step as StepData;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownStepException;
use webignition\BasilParser\Loader\DataSetLoader;
use webignition\BasilParser\Loader\PageLoader;
use webignition\BasilParser\Loader\StepLoader;
use webignition\BasilParser\Provider\DataSet\DataSetProviderInterface;
use webignition\BasilParser\Provider\DataSet\DeferredDataSetProvider;
use webignition\BasilParser\Provider\DataSet\EmptyDataSetProvider;
use webignition\BasilParser\Provider\DataSet\PopulatedDataSetProvider;
use webignition\BasilParser\Exception\UnknownDataProviderException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Provider\Page\DeferredPageProvider;
use webignition\BasilParser\Provider\Page\EmptyPageProvider;
use webignition\BasilParser\Provider\Page\PageProviderInterface;
use webignition\BasilParser\Provider\Page\PopulatedPageProvider;
use webignition\BasilParser\Provider\Step\DeferredStepProvider;
use webignition\BasilParser\Provider\Step\EmptyStepProvider;
use webignition\BasilParser\Provider\Step\StepProviderInterface;
use webignition\BasilParser\Tests\Services\FixturePathFinder;

class StepBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var StepBuilder
     */
    private $stepBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stepBuilder = StepBuilder::createBuilder();
    }

    /**
     * @dataProvider buildSuccessDataProvider
     */
    public function testBuildSuccess(
        StepData $stepData,
        StepProviderInterface $stepProvider,
        DataSetProviderInterface $dataSetProvider,
        PageProviderInterface $pageProvider,
        StepInterface $expectedStep
    ) {
        $step = $this->stepBuilder->build(
            $stepData,
            $stepProvider,
            $dataSetProvider,
            $pageProvider
        );

        $this->assertInstanceOf(StepInterface::class, $step);
        $this->assertEquals($expectedStep, $step);
    }

    public function buildSuccessDataProvider(): array
    {
        $simpleCssSelectorIdentifier = new ElementIdentifier(
            LiteralValue::createCssSelectorValue('.selector')
        );

        $buttonCssSelectorIdentifier = new ElementIdentifier(
            LiteralValue::createCssSelectorValue('.button')
        );

        $headingCssSelectorIdentifier = new ElementIdentifier(
            LiteralValue::createCssSelectorValue('.heading')
        );

        return [
            'no imports, no actions, no assertions' => [
                'stepData' => new StepData([]),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => new Step([], []),
            ],
            'no imports, empty actions, empty assertions' => [
                'stepData' => new StepData([
                    StepData::KEY_ACTIONS => [],
                    StepData::KEY_ASSERTIONS => [],
                ]),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => new Step([], []),
            ],
            'unused invalid imports, empty actions, empty assertions' => [
                'stepData' => new StepData([
                    StepData::KEY_ACTIONS => [],
                    StepData::KEY_ASSERTIONS => [],
                ]),
                'stepProvider' => new DeferredStepProvider(
                    StepLoader::createLoader(),
                    [
                        'step_import_name' => 'invalid.yml',
                    ]
                ),
                'dataSetProvider' => new DeferredDataSetProvider(
                    DataSetLoader::createLoader(),
                    [
                        'data_provider_name' => 'invalid.yml',
                    ]
                ),
                'pageProvider' => new DeferredPageProvider(
                    PageLoader::createLoader(),
                    [
                        'page_import_name' => 'invalid.yml',
                    ]
                ),
                'expectedStep' => new Step([], []),
            ],
            'no imports, has actions, has assertions' => [
                'stepData' => new StepData([
                    StepData::KEY_ACTIONS => [
                        'click ".selector"',
                    ],
                    StepData::KEY_ASSERTIONS => [
                        '$page.title is "Example"',
                    ],
                ]),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => new Step(
                    [
                        new InteractionAction(
                            'click ".selector"',
                            ActionTypes::CLICK,
                            $simpleCssSelectorIdentifier,
                            '".selector"'
                        )
                    ],
                    [
                        new Assertion(
                            '$page.title is "Example"',
                            new ObjectValue(
                                ValueTypes::PAGE_OBJECT_PROPERTY,
                                '$page.title',
                                ObjectNames::PAGE,
                                'title'
                            ),
                            AssertionComparisons::IS,
                            LiteralValue::createStringValue('Example')
                        )
                    ]
                ),
            ],
            'no imports, inline step with page model element references' => [
                'stepData' => new StepData([
                    StepData::KEY_ACTIONS => [
                        'set page_import_name.elements.element_name to "example"',
                    ],
                    StepData::KEY_ASSERTIONS => [
                        'page_import_name.elements.element_name is "example"',
                    ],
                ]),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'pageProvider' => new PopulatedPageProvider([
                    'page_import_name' => new Page(
                        new Uri('http://example.com'),
                        new IdentifierCollection([
                            $simpleCssSelectorIdentifier->withName('element_name')
                        ])
                    )
                ]),
                'expectedStep' => new Step(
                    [
                        new InputAction(
                            'set page_import_name.elements.element_name to "example"',
                            $simpleCssSelectorIdentifier->withName('element_name'),
                            LiteralValue::createStringValue('example'),
                            'page_import_name.elements.element_name to "example"'
                        ),
                    ],
                    [
                        new Assertion(
                            'page_import_name.elements.element_name is "example"',
                            new ElementValue($simpleCssSelectorIdentifier->withName('element_name')),
                            AssertionComparisons::IS,
                            LiteralValue::createStringValue('example')
                        )
                    ]
                ),
            ],
            'import step' => [
                'stepData' => new StepData([
                    StepData::KEY_USE => 'step_import_name',
                ]),
                'stepProvider' => new DeferredStepProvider(
                    StepLoader::createLoader(),
                    [
                        'step_import_name' => FixturePathFinder::find('Step/no-parameters.yml'),
                    ]
                ),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => new Step(
                    [
                        new InteractionAction(
                            'click ".button"',
                            ActionTypes::CLICK,
                            $buttonCssSelectorIdentifier,
                            '".button"'
                        )
                    ],
                    [
                        new Assertion(
                            '".heading" includes "Hello World"',
                            new ElementValue($headingCssSelectorIdentifier),
                            AssertionComparisons::INCLUDES,
                            LiteralValue::createStringValue('Hello World')
                        ),
                    ]
                ),
            ],
            'inline data' => [
                'stepData' => new StepData([
                    StepData::KEY_USE => 'step_import_name',
                    StepData::KEY_DATA => [
                        [
                            'expected_title' => 'Foo',
                        ],
                        [
                            'expected_title' => 'Bar',
                        ],
                    ],
                ]),
                'stepProvider' => new DeferredStepProvider(
                    StepLoader::createLoader(),
                    [
                        'step_import_name' => FixturePathFinder::find('Step/data-parameters.yml'),
                    ]
                ),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => (new Step(
                    [
                        new InteractionAction(
                            'click ".button"',
                            ActionTypes::CLICK,
                            $buttonCssSelectorIdentifier,
                            '".button"'
                        )
                    ],
                    [
                        new Assertion(
                            '".heading" includes $data.expected_title',
                            new ElementValue($headingCssSelectorIdentifier),
                            AssertionComparisons::INCLUDES,
                            new ObjectValue(
                                ValueTypes::DATA_PARAMETER,
                                '$data.expected_title',
                                'data',
                                'expected_title'
                            )
                        ),
                    ]
                ))->withDataSetCollection(new DataSetCollection([
                    new DataSet('0', [
                        'expected_title' => 'Foo',
                    ]),
                    new DataSet('1', [
                        'expected_title' => 'Bar',
                    ]),
                ])),
            ],
            'imported data' => [
                'stepData' => new StepData([
                    StepData::KEY_USE => 'step_import_name',
                    StepData::KEY_DATA => 'data_provider_name',
                ]),
                'stepProvider' => new DeferredStepProvider(
                    StepLoader::createLoader(),
                    [
                        'step_import_name' => FixturePathFinder::find('Step/data-parameters.yml'),
                    ]
                ),
                'dataSetProvider' => new PopulatedDataSetProvider([
                    'data_provider_name' => new DataSetCollection([
                        new DataSet('0', [
                            'expected_title' => 'Foo',
                        ]),
                        new DataSet('1', [
                            'expected_title' => 'Bar',
                        ]),
                    ]),
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => (new Step(
                    [
                        new InteractionAction(
                            'click ".button"',
                            ActionTypes::CLICK,
                            $buttonCssSelectorIdentifier,
                            '".button"'
                        )
                    ],
                    [
                        new Assertion(
                            '".heading" includes $data.expected_title',
                            new ElementValue($headingCssSelectorIdentifier),
                            AssertionComparisons::INCLUDES,
                            new ObjectValue(
                                ValueTypes::DATA_PARAMETER,
                                '$data.expected_title',
                                'data',
                                'expected_title'
                            )
                        ),
                    ]
                ))->withDataSetCollection(new DataSetCollection([
                    new DataSet('0', [
                        'expected_title' => 'Foo',
                    ]),
                    new DataSet('1', [
                        'expected_title' => 'Bar',
                    ]),
                ])),
            ],
            'element parameters' => [
                'stepData' => new StepData([
                    StepData::KEY_USE => 'step_import_name',
                    StepData::KEY_ELEMENTS => [
                        'heading' => 'page_import_name.elements.heading',
                    ],
                ]),
                'stepProvider' => new DeferredStepProvider(
                    StepLoader::createLoader(),
                    [
                        'step_import_name' => FixturePathFinder::find('Step/element-parameters.yml'),
                    ]
                ),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'pageProvider' => new PopulatedPageProvider([
                    'page_import_name' => new Page(
                        new Uri('http://example.com'),
                        new IdentifierCollection([
                            $headingCssSelectorIdentifier->withName('heading'),
                        ])
                    ),
                ]),
                'expectedStep' =>
                    (new Step(
                        [
                            new InteractionAction(
                                'click ".button"',
                                ActionTypes::CLICK,
                                $buttonCssSelectorIdentifier,
                                '".button"'
                            )
                        ],
                        [
                            new Assertion(
                                '$elements.heading includes "Hello World"',
                                new ObjectValue(
                                    ValueTypes::ELEMENT_PARAMETER,
                                    '$elements.heading',
                                    ObjectNames::ELEMENT,
                                    'heading'
                                ),
                                AssertionComparisons::INCLUDES,
                                LiteralValue::createStringValue('Hello World')
                            ),
                        ]
                    ))->withIdentifierCollection(new IdentifierCollection([
                        $headingCssSelectorIdentifier->withName('heading'),
                    ])),
            ],
            'actions and assertions with environment parameters' => [
                'stepData' => new StepData([
                    StepData::KEY_ACTIONS => [
                        'set page_import_name.elements.element_name to $env.KEY1',
                    ],
                    StepData::KEY_ASSERTIONS => [
                        'page_import_name.elements.element_name is $env.KEY2|"default"',
                    ],
                ]),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'pageProvider' => new PopulatedPageProvider([
                    'page_import_name' => new Page(
                        new Uri('http://example.com'),
                        new IdentifierCollection([
                            $simpleCssSelectorIdentifier->withName('element_name')
                        ])
                    )
                ]),
                'expectedStep' => new Step(
                    [
                        new InputAction(
                            'set page_import_name.elements.element_name to $env.KEY1',
                            $simpleCssSelectorIdentifier->withName('element_name'),
                            new EnvironmentValue(
                                '$env.KEY1',
                                'KEY1'
                            ),
                            'page_import_name.elements.element_name to $env.KEY1'
                        ),
                    ],
                    [
                        new Assertion(
                            'page_import_name.elements.element_name is $env.KEY2|"default"',
                            new ElementValue($simpleCssSelectorIdentifier->withName('element_name')),
                            AssertionComparisons::IS,
                            new EnvironmentValue(
                                '$env.KEY2|"default"',
                                'KEY2',
                                'default'
                            )
                        )
                    ]
                ),
            ],
        ];
    }

    public function testBuildUseUnknownStepImport()
    {
        $this->expectException(UnknownStepException::class);
        $this->expectExceptionMessage('Unknown step "unknown_step_import_name"');

        $this->stepBuilder->build(
            new StepData([
                StepData::KEY_USE => 'unknown_step_import_name',
            ]),
            new EmptyStepProvider(),
            new EmptyDataSetProvider(),
            new EmptyPageProvider()
        );
    }

    public function testBuildUseUnknownDataProviderImport()
    {
        $this->expectException(UnknownDataProviderException::class);
        $this->expectExceptionMessage('Unknown data provider "unknown_data_provider_name"');

        $this->stepBuilder->build(
            new StepData([
                StepData::KEY_USE => 'step_import_name',
                StepData::KEY_DATA => 'unknown_data_provider_name',
            ]),
            new DeferredStepProvider(
                StepLoader::createLoader(),
                [
                    'step_import_name' => FixturePathFinder::find('Step/data-parameters.yml'),
                ]
            ),
            new EmptyDataSetProvider(),
            new EmptyPageProvider()
        );
    }

    public function testBuildUseUnknownPageImport()
    {
        $this->expectException(UnknownPageException::class);
        $this->expectExceptionMessage('Unknown page "page_import_name"');

        $this->stepBuilder->build(
            new StepData([
                StepData::KEY_USE => 'step_import_name',
                StepData::KEY_ELEMENTS => [
                    'heading' => 'page_import_name.elements.heading',
                ],
            ]),
            new DeferredStepProvider(
                StepLoader::createLoader(),
                [
                    'step_import_name' => FixturePathFinder::find('Step/element-parameters.yml'),
                ]
            ),
            new EmptyDataSetProvider(),
            new EmptyPageProvider()
        );
    }

    public function testBuildUseUnknownPageElement()
    {
        $this->expectException(UnknownPageElementException::class);
        $this->expectExceptionMessage('Unknown page element "not-heading" in page "page_import_name"');

        $this->stepBuilder->build(
            new StepData([
                StepData::KEY_USE => 'step_import_name',
                StepData::KEY_ELEMENTS => [
                    'not-heading' => 'page_import_name.elements.not-heading',
                ],
            ]),
            new DeferredStepProvider(
                StepLoader::createLoader(),
                [
                    'step_import_name' => FixturePathFinder::find('Step/element-parameters.yml'),
                ]
            ),
            new EmptyDataSetProvider(),
            new PopulatedPageProvider([
                'page_import_name' => new Page(
                    new Uri('http://example.com'),
                    new IdentifierCollection([
                        new ElementIdentifier(
                            LiteralValue::createCssSelectorValue('.heading'),
                            1,
                            'heading'
                        )
                    ])
                ),
            ])
        );
    }

    public function testBuildUseInvalidPageElementReference()
    {
        $this->expectException(MalformedPageElementReferenceException::class);
        $this->expectExceptionMessage('Malformed page element reference "page_import_name.foo.heading"');

        $this->stepBuilder->build(
            new StepData([
                StepData::KEY_USE => 'step_import_name',
                StepData::KEY_ELEMENTS => [
                    'heading' => 'page_import_name.foo.heading',
                ],
            ]),
            new DeferredStepProvider(
                StepLoader::createLoader(),
                [
                    'step_import_name' => FixturePathFinder::find('Step/element-parameters.yml'),
                ]
            ),
            new EmptyDataSetProvider(),
            new EmptyPageProvider()
        );
    }
}
