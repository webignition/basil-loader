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
use webignition\BasilModel\Identifier\Identifier;
use webignition\BasilModel\Identifier\IdentifierTypes;
use webignition\BasilModel\Page\Page;
use webignition\BasilModel\Step\Step;
use webignition\BasilModel\Step\StepInterface;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\Value;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilParser\Builder\StepBuilder;
use webignition\BasilDataStructure\Step as StepData;
use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownStepException;
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
use webignition\BasilParser\Tests\Services\DataSetLoaderFactory;
use webignition\BasilParser\Tests\Services\FixturePathFinder;
use webignition\BasilParser\Tests\Services\PageLoaderFactory;
use webignition\BasilParser\Tests\Services\StepBuilderFactory;
use webignition\BasilParser\Tests\Services\StepLoaderFactory;

class StepBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var StepBuilder
     */
    private $stepBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stepBuilder = StepBuilderFactory::create();
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
        $simpleCssSelectorIdentifier = new Identifier(
            IdentifierTypes::CSS_SELECTOR,
            new Value(
                ValueTypes::STRING,
                '.selector'
            )
        );

        $buttonCssSelectorIdentifier = new Identifier(
            IdentifierTypes::CSS_SELECTOR,
            new Value(
                ValueTypes::STRING,
                '.button'
            )
        );

        $headingCssSelectorIdentifier = new Identifier(
            IdentifierTypes::CSS_SELECTOR,
            new Value(
                ValueTypes::STRING,
                '.heading'
            )
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
                    StepLoaderFactory::create(),
                    [
                        'step_import_name' => 'invalid.yml',
                    ]
                ),
                'dataSetProvider' => new DeferredDataSetProvider(
                    DataSetLoaderFactory::create(),
                    [
                        'data_provider_name' => 'invalid.yml',
                    ]
                ),
                'pageProvider' => new DeferredPageProvider(
                    PageLoaderFactory::create(),
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
                                'Example'
                            )
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
                        [
                            'element_name' => $simpleCssSelectorIdentifier
                        ]
                    )
                ]),
                'expectedStep' => new Step(
                    [
                        new InputAction(
                            'set page_import_name.elements.element_name to "example"',
                            $simpleCssSelectorIdentifier,
                            new Value(
                                ValueTypes::STRING,
                                'example'
                            ),
                            'page_import_name.elements.element_name to "example"'
                        ),
                    ],
                    [
                        new Assertion(
                            'page_import_name.elements.element_name is "example"',
                            $simpleCssSelectorIdentifier,
                            AssertionComparisons::IS,
                            new Value(
                                ValueTypes::STRING,
                                'example'
                            )
                        )
                    ]
                ),
            ],
            'import step' => [
                'stepData' => new StepData([
                    StepData::KEY_USE => 'step_import_name',
                ]),
                'stepProvider' => new DeferredStepProvider(
                    StepLoaderFactory::create(),
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
                            $headingCssSelectorIdentifier,
                            AssertionComparisons::INCLUDES,
                            new Value(
                                ValueTypes::STRING,
                                'Hello World'
                            )
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
                    StepLoaderFactory::create(),
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
                            $headingCssSelectorIdentifier,
                            AssertionComparisons::INCLUDES,
                            new ObjectValue(
                                ValueTypes::DATA_PARAMETER,
                                '$data.expected_title',
                                'data',
                                'expected_title'
                            )
                        ),
                    ]
                ))->withDataSets([
                    new DataSet([
                        'expected_title' => 'Foo',
                    ]),
                    new DataSet([
                        'expected_title' => 'Bar',
                    ]),
                ]),
            ],
            'imported data' => [
                'stepData' => new StepData([
                    StepData::KEY_USE => 'step_import_name',
                    StepData::KEY_DATA => 'data_provider_name',
                ]),
                'stepProvider' => new DeferredStepProvider(
                    StepLoaderFactory::create(),
                    [
                        'step_import_name' => FixturePathFinder::find('Step/data-parameters.yml'),
                    ]
                ),
                'dataSetProvider' => new PopulatedDataSetProvider([
                    'data_provider_name' => [
                        new DataSet([
                            'expected_title' => 'Foo',
                        ]),
                        new DataSet([
                            'expected_title' => 'Bar',
                        ]),
                    ],
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
                            $headingCssSelectorIdentifier,
                            AssertionComparisons::INCLUDES,
                            new ObjectValue(
                                ValueTypes::DATA_PARAMETER,
                                '$data.expected_title',
                                'data',
                                'expected_title'
                            )
                        ),
                    ]
                ))->withDataSets([
                    new DataSet([
                        'expected_title' => 'Foo',
                    ]),
                    new DataSet([
                        'expected_title' => 'Bar',
                    ]),
                ]),
            ],
            'element parameters' => [
                'stepData' => new StepData([
                    StepData::KEY_USE => 'step_import_name',
                    StepData::KEY_ELEMENTS => [
                        'heading' => 'page_import_name.elements.heading',
                    ],
                ]),
                'stepProvider' => new DeferredStepProvider(
                    StepLoaderFactory::create(),
                    [
                        'step_import_name' => FixturePathFinder::find('Step/element-parameters.yml'),
                    ]
                ),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'pageProvider' => new PopulatedPageProvider([
                    'page_import_name' => new Page(
                        new Uri('http://example.com'),
                        [
                            'heading' => new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                new Value(
                                    ValueTypes::STRING,
                                    '.heading'
                                ),
                                null,
                                'heading'
                            ),
                        ]
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
                                new Identifier(
                                    IdentifierTypes::ELEMENT_PARAMETER,
                                    new ObjectValue(
                                        ValueTypes::ELEMENT_PARAMETER,
                                        '$elements.heading',
                                        'elements',
                                        'heading'
                                    )
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
                            new Value(
                                ValueTypes::STRING,
                                '.heading'
                            ),
                            null,
                            'heading'
                        ),
                    ]),
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
                StepLoaderFactory::create(),
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
                StepLoaderFactory::create(),
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
                StepLoaderFactory::create(),
                [
                    'step_import_name' => FixturePathFinder::find('Step/element-parameters.yml'),
                ]
            ),
            new EmptyDataSetProvider(),
            new PopulatedPageProvider([
                'page_import_name' => new Page(
                    new Uri('http://example.com'),
                    [
                        'heading' => new Identifier(
                            IdentifierTypes::CSS_SELECTOR,
                            new Value(
                                ValueTypes::STRING,
                                '.heading'
                            ),
                            null,
                            'heading'
                        )
                    ]
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
                StepLoaderFactory::create(),
                [
                    'step_import_name' => FixturePathFinder::find('Step/element-parameters.yml'),
                ]
            ),
            new EmptyDataSetProvider(),
            new EmptyPageProvider()
        );
    }
}
