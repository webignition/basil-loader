<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Resolver;

use Nyholm\Psr7\Uri;
use webignition\BasilModel\Action\InputAction;
use webignition\BasilModel\Action\WaitAction;
use webignition\BasilModel\Assertion\Assertion;
use webignition\BasilModel\Assertion\AssertionComparisons;
use webignition\BasilModel\Assertion\AssertionInterface;
use webignition\BasilModel\DataSet\DataSet;
use webignition\BasilModel\Identifier\Identifier;
use webignition\BasilModel\Identifier\IdentifierTypes;
use webignition\BasilModel\Page\Page;
use webignition\BasilModel\Step\PendingImportResolutionStep;
use webignition\BasilModel\Step\Step;
use webignition\BasilModel\Step\StepInterface;
use webignition\BasilModel\Value\Value;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilParser\Provider\DataSet\DataSetProviderInterface;
use webignition\BasilParser\Provider\DataSet\EmptyDataSetProvider;
use webignition\BasilParser\Provider\DataSet\PopulatedDataSetProvider;
use webignition\BasilParser\Provider\Page\EmptyPageProvider;
use webignition\BasilParser\Provider\Page\PageProviderInterface;
use webignition\BasilParser\Provider\Page\PopulatedPageProvider;
use webignition\BasilParser\Provider\Step\EmptyStepProvider;
use webignition\BasilParser\Provider\Step\PopulatedStepProvider;
use webignition\BasilParser\Provider\Step\StepProviderInterface;
use webignition\BasilParser\Resolver\StepResolver;
use webignition\BasilParser\Tests\Services\StepResolverFactory;

class StepResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var StepResolver
     */
    private $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = StepResolverFactory::create();
    }

    /**
     * @dataProvider resolveStepImportDataProvider
     */
    public function testResolveStepImport(
        StepInterface $step,
        StepProviderInterface $stepProvider,
        StepInterface $expectedStep
    ) {
        $resolvedStep = $this->resolver->resolve(
            $step,
            $stepProvider,
            new EmptyDataSetProvider(),
            new EmptyPageProvider()
        );

        $this->assertEquals($expectedStep, $resolvedStep);
    }

    public function resolveStepImportDataProvider(): array
    {
        return [
            'empty step imports empty step' => [
                'step' => new PendingImportResolutionStep(new Step([], []), 'step_import_name', ''),
                'stepProvider' => new PopulatedStepProvider([
                    'step_import_name' => new Step([], []),
                ]),
                'expectedStep' => new Step([], []),
            ],
            'empty step imports non-empty step' => [
                'step' => new PendingImportResolutionStep(new Step([], []), 'step_import_name', ''),
                'stepProvider' => new PopulatedStepProvider([
                    'step_import_name' => new Step([
                        new WaitAction('1'),
                    ], [
                        new Assertion('".selector" exists', null, null)
                    ]),
                ]),
                'expectedStep' => new Step([
                    new WaitAction('1'),
                ], [
                    new Assertion('".selector" exists', null, null)
                ]),
            ],
            'step with actions imports step with actions' => [
                'step' => new PendingImportResolutionStep(
                    new Step([
                        new WaitAction('2'),
                    ], []),
                    'step_import_name',
                    ''
                ),
                'stepProvider' => new PopulatedStepProvider([
                    'step_import_name' => new Step([
                        new WaitAction('1'),
                    ], [
                        new Assertion('".selector" exists', null, null)
                    ]),
                ]),
                'expectedStep' => new Step([
                    new WaitAction('1'),
                    new WaitAction('2'),
                ], [
                    new Assertion('".selector" exists', null, null)
                ]),
            ],
            'step with assertions imports step with assertions' => [
                'step' => new PendingImportResolutionStep(
                    new Step([], [
                        new Assertion('".selector2" exists', null, null)
                    ]),
                    'step_import_name',
                    ''
                ),
                'stepProvider' => new PopulatedStepProvider([
                    'step_import_name' => new Step([], [
                        new Assertion('".selector1" exists', null, null)
                    ]),
                ]),
                'expectedStep' => new Step([], [
                    new Assertion('".selector1" exists', null, null),
                    new Assertion('".selector2" exists', null, null),
                ]),
            ],
            'deferred' => [
                'step' => new PendingImportResolutionStep(new Step([], []), 'deferred_step_import_name', ''),
                'stepProvider' => new PopulatedStepProvider([
                    'deferred_step_import_name' => new PendingImportResolutionStep(
                        new Step([], []),
                        'step_import_name',
                        ''
                    ),
                    'step_import_name' => new Step([
                        new WaitAction('1'),
                    ], [
                        new Assertion('".selector" exists', null, null)
                    ]),
                ]),
                'expectedStep' => new Step([
                    new WaitAction('1'),
                ], [
                    new Assertion('".selector" exists', null, null)
                ]),
            ],
        ];
    }

    /**
     * @dataProvider resolveDataProviderImportDataProvider
     */
    public function testResolveDataProviderImport(
        StepInterface $step,
        DataSetProviderInterface $dataSetProvider,
        StepInterface $expectedStep
    ) {
        $resolvedStep = $this->resolver->resolve(
            $step,
            new EmptyStepProvider(),
            $dataSetProvider,
            new EmptyPageProvider()
        );

        $this->assertEquals($expectedStep, $resolvedStep);
    }

    public function resolveDataProviderImportDataProvider(): array
    {
        return [
            'step imports from data provider' => [
                'step' => new PendingImportResolutionStep(
                    new Step([], []),
                    '',
                    'data_provider_import_name'
                ),
                'dataSetProvider' => new PopulatedDataSetProvider([
                    'data_provider_import_name' => [
                        new DataSet([
                            'foo' => 'bar',
                        ])
                    ],
                ]),
                'expectedStep' => (new Step([], []))->withDataSets([
                    new DataSet([
                        'foo' => 'bar',
                    ])
                ]),
            ],
        ];
    }

    /**
     * @dataProvider resolveActionsDataProvider
     */
    public function testResolveActions(
        StepInterface $step,
        PageProviderInterface $pageProvider,
        StepInterface $expectedStep
    ) {
        $resolvedStep = $this->resolver->resolve(
            $step,
            new EmptyStepProvider(),
            new EmptyDataSetProvider(),
            $pageProvider
        );

        $this->assertEquals($expectedStep, $resolvedStep);
    }

    public function resolveActionsDataProvider(): array
    {
        return [
            'no actions' => [
                'step' => new Step([], []),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => new Step([], []),
            ],
            'no resolvable actions' => [
                'step' => new Step([
                    new WaitAction('30'),
                ], []),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => new Step([
                    new WaitAction('30'),
                ], []),
            ],
            'has resolvable actions' => [
                'step' => new Step([
                    new InputAction(
                        new Identifier(
                            IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                            new Value(
                                ValueTypes::STRING,
                                'page_import_name.elements.element_name'
                            )
                        ),
                        new Value(
                            ValueTypes::STRING,
                            'value'
                        ),
                        'page_import_name.elements.element_name to "value"'
                    )
                ], []),
                'pageProvider' => new PopulatedPageProvider([
                    'page_import_name' => new Page(
                        new Uri('http://example.com/'),
                        [
                            'element_name' => new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                new Value(
                                    ValueTypes::STRING,
                                    '.selector'
                                )
                            )
                        ]
                    )
                ]),
                'expectedStep' => new Step([
                    new InputAction(
                        new Identifier(
                            IdentifierTypes::CSS_SELECTOR,
                            new Value(
                                ValueTypes::STRING,
                                '.selector'
                            )
                        ),
                        new Value(
                            ValueTypes::STRING,
                            'value'
                        ),
                        'page_import_name.elements.element_name to "value"'
                    )
                ], []),
            ],
        ];
    }

    /**
     * @dataProvider resolveAssertionsDataProvider
     */
    public function testResolveAssertions(
        StepInterface $step,
        PageProviderInterface $pageProvider,
        StepInterface $expectedStep
    ) {
        $resolvedStep = $this->resolver->resolve(
            $step,
            new EmptyStepProvider(),
            new EmptyDataSetProvider(),
            $pageProvider
        );

        $this->assertEquals($expectedStep, $resolvedStep);
    }

    public function resolveAssertionsDataProvider(): array
    {
        return [
            'no assertions' => [
                'step' => new Step([], [
                    \Mockery::mock(AssertionInterface::class),
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => new Step([], [
                    \Mockery::mock(AssertionInterface::class),
                ]),
            ],
            'no resolvable assertions' => [
                'step' => new Step([], []),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => new Step([], []),
            ],
            'has resolvable assertions' => [
                'step' => new Step([], [
                    new Assertion(
                        'page_import_name.elements.element_name exists',
                        new Identifier(
                            IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                            new Value(
                                ValueTypes::STRING,
                                'page_import_name.elements.element_name'
                            )
                        ),
                        AssertionComparisons::EXISTS
                    ),
                ]),
                'pageProvider' => new PopulatedPageProvider([
                    'page_import_name' => new Page(
                        new Uri('http://example.com/'),
                        [
                            'element_name' => new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                new Value(
                                    ValueTypes::STRING,
                                    '.selector'
                                )
                            )
                        ]
                    )
                ]),
                'expectedStep' => new Step([], [
                    new Assertion(
                        'page_import_name.elements.element_name exists',
                        new Identifier(
                            IdentifierTypes::CSS_SELECTOR,
                            new Value(
                                ValueTypes::STRING,
                                '.selector'
                            )
                        ),
                        AssertionComparisons::EXISTS
                    ),
                ]),
            ],
        ];
    }

    /**
     * @dataProvider resolveElementIdentifiersDataProvider
     */
    public function testResolveElementIdentifiers(
        StepInterface $step,
        PageProviderInterface $pageProvider,
        StepInterface $expectedStep
    ) {
        $resolvedStep = $this->resolver->resolve(
            $step,
            new EmptyStepProvider(),
            new EmptyDataSetProvider(),
            $pageProvider
        );

        $this->assertEquals($expectedStep, $resolvedStep);
    }

    public function resolveElementIdentifiersDataProvider(): array
    {
        return [
            'no element identifiers' => [
                'step' => new Step([], []),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => new Step([], []),
            ],
            'no resolvable element identifiers' => [
                'step' => (new Step([], []))->withElementIdentifiers([
                    new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        new Value(
                            ValueTypes::STRING,
                            '.selector'
                        )
                    ),
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => (new Step([], []))->withElementIdentifiers([
                    new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        new Value(
                            ValueTypes::STRING,
                            '.selector'
                        )
                    ),
                ])
            ],
            'has resolvable element identifiers' => [
                'step' => (new Step([], []))->withElementIdentifiers([
                    'element_name' => new Identifier(
                        IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                        new Value(
                            ValueTypes::STRING,
                            'page_import_name.elements.element_name'
                        ),
                        null,
                        'identifier_name'
                    ),
                ]),
                'pageProvider' => new PopulatedPageProvider([
                    'page_import_name' => new Page(
                        new Uri('http://example.com/'),
                        [
                            'element_name' => new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                new Value(
                                    ValueTypes::STRING,
                                    '.selector'
                                )
                            )
                        ]
                    )
                ]),
                'expectedStep' => (new Step([], []))->withElementIdentifiers([
                    'element_name' => new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        new Value(
                            ValueTypes::STRING,
                            '.selector'
                        ),
                        null,
                        'identifier_name'
                    ),
                ])
            ],
        ];
    }
}
