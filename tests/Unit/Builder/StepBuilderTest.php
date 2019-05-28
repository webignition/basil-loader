<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Builder;

use Nyholm\Psr7\Uri;
use webignition\BasilParser\Builder\StepBuilder;
use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownStepException;
use webignition\BasilParser\Provider\DataSet\DataSetProviderInterface;
use webignition\BasilParser\Provider\DataSet\DeferredDataSetProvider;
use webignition\BasilParser\Provider\DataSet\EmptyDataSetProvider;
use webignition\BasilParser\Provider\DataSet\PopulatedDataSetProvider;
use webignition\BasilParser\Exception\UnknownDataProviderException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Factory\StepFactory;
use webignition\BasilParser\Model\Action\ActionTypes;
use webignition\BasilParser\Model\Action\InputAction;
use webignition\BasilParser\Model\Action\InteractionAction;
use webignition\BasilParser\Model\Assertion\Assertion;
use webignition\BasilParser\Model\Assertion\AssertionComparisons;
use webignition\BasilParser\Model\DataSet\DataSet;
use webignition\BasilParser\Model\Identifier\Identifier;
use webignition\BasilParser\Model\Identifier\IdentifierTypes;
use webignition\BasilParser\Model\Page\Page;
use webignition\BasilParser\Model\Step\Step;
use webignition\BasilParser\Model\Step\StepInterface;
use webignition\BasilParser\Model\Value\Value;
use webignition\BasilParser\Model\Value\ValueTypes;
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
        array $stepData,
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
        return [
            'no imports, no actions, no assertions' => [
                'stepData' => [],
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => new Step([], []),
            ],
            'no imports, empty actions, empty assertions' => [
                'stepData' => [
                    StepFactory::KEY_ACTIONS => [],
                    StepFactory::KEY_ASSERTIONS => [],
                ],
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => new Step([], []),
            ],
            'unused invalid imports, empty actions, empty assertions' => [
                'stepData' => [
                    StepFactory::KEY_ACTIONS => [],
                    StepFactory::KEY_ASSERTIONS => [],
                ],
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
                'stepData' => [
                    StepFactory::KEY_ACTIONS => [
                        'click ".selector"',
                    ],
                    StepFactory::KEY_ASSERTIONS => [
                        '$page.title is "Example"',
                    ],
                ],
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => new Step(
                    [
                        new InteractionAction(
                            ActionTypes::CLICK,
                            new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                '.selector'
                            ),
                            '".selector"'
                        )
                    ],
                    [
                        new Assertion(
                            '$page.title is "Example"',
                            new Identifier(
                                IdentifierTypes::PAGE_OBJECT_PARAMETER,
                                '$page.title'
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
                'stepData' => [
                    StepFactory::KEY_ACTIONS => [
                        'set page_import_name.elements.element_name to "example"',
                    ],
                    StepFactory::KEY_ASSERTIONS => [
                        'page_import_name.elements.element_name is "example"',
                    ],
                ],
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'pageProvider' => new PopulatedPageProvider([
                    'page_import_name' => new Page(
                        new Uri('http://example.com'),
                        [
                            'element_name' => new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                '.selector'
                            )
                        ]
                    )
                ]),
                'expectedStep' => new Step(
                    [
                        new InputAction(
                            new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                '.selector'
                            ),
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
                            new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                '.selector'
                            ),
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
                'stepData' => [
                    StepBuilder::KEY_USE => 'step_import_name',
                ],
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
            ],
            'inline data' => [
                'stepData' => [
                    StepBuilder::KEY_USE => 'step_import_name',
                    StepBuilder::KEY_DATA => [
                        [
                            'expected_title' => 'Foo',
                        ],
                        [
                            'expected_title' => 'Bar',
                        ],
                    ],
                ],
                'stepProvider' => new DeferredStepProvider(
                    StepLoaderFactory::create(),
                    [
                        'step_import_name' => FixturePathFinder::find('Step/data-parameters.yml'),
                    ]
                ),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => new Step(
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
                ),
            ],
            'imported data' => [
                'stepData' => [
                    StepBuilder::KEY_USE => 'step_import_name',
                    StepBuilder::KEY_DATA => 'data_provider_name',
                ],
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
                    new DataSet([
                        'expected_title' => 'Foo',
                    ]),
                    new DataSet([
                        'expected_title' => 'Bar',
                    ]),
                ]),
            ],
            'element parameters' => [
                'stepData' => [
                    StepBuilder::KEY_USE => 'step_import_name',
                    StepBuilder::KEY_ELEMENTS => [
                        'heading' => 'page_import_name.elements.heading',
                    ],
                ],
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
                                '.heading',
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
            ],
        ];
    }

    public function testBuildUseUnknownStepImport()
    {
        $this->expectException(UnknownStepException::class);
        $this->expectExceptionMessage('Unknown step "unknown_step_import_name"');

        $this->stepBuilder->build(
            [
                StepBuilder::KEY_USE => 'unknown_step_import_name',
            ],
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
            [
                StepBuilder::KEY_USE => 'step_import_name',
                StepBuilder::KEY_DATA => 'unknown_data_provider_name',
            ],
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
            [
                StepBuilder::KEY_USE => 'step_import_name',
                StepBuilder::KEY_ELEMENTS => [
                    'heading' => 'page_import_name.elements.heading',
                ],
            ],
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
            [
                StepBuilder::KEY_USE => 'step_import_name',
                StepBuilder::KEY_ELEMENTS => [
                    'not-heading' => 'page_import_name.elements.not-heading',
                ],
            ],
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
                            '.heading',
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
            [
                StepBuilder::KEY_USE => 'step_import_name',
                StepBuilder::KEY_ELEMENTS => [
                    'heading' => 'page_import_name.foo.heading',
                ],
            ],
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
