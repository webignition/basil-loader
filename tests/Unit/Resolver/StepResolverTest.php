<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Resolver;

use Nyholm\Psr7\Uri;
use webignition\BasilContextAwareException\ContextAwareExceptionInterface;
use webignition\BasilContextAwareException\ExceptionContext\ExceptionContext;
use webignition\BasilContextAwareException\ExceptionContext\ExceptionContextInterface;
use webignition\BasilModel\Action\ActionTypes;
use webignition\BasilModel\Action\InputAction;
use webignition\BasilModel\Action\InteractionAction;
use webignition\BasilModel\Action\WaitAction;
use webignition\BasilModel\Assertion\Assertion;
use webignition\BasilModel\Assertion\AssertionComparisons;
use webignition\BasilModel\DataSet\DataSet;
use webignition\BasilModel\DataSet\DataSetCollection;
use webignition\BasilModel\Identifier\ElementIdentifier;
use webignition\BasilModel\Identifier\Identifier;
use webignition\BasilModel\Identifier\IdentifierCollection;
use webignition\BasilModel\Identifier\IdentifierTypes;
use webignition\BasilModel\Page\Page;
use webignition\BasilModel\Step\PendingImportResolutionStep;
use webignition\BasilModel\Step\Step;
use webignition\BasilModel\Step\StepInterface;
use webignition\BasilModel\Value\ElementValue;
use webignition\BasilModel\Value\LiteralValue;
use webignition\BasilModel\Value\ObjectNames;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilModelFactory\InvalidPageElementIdentifierException;
use webignition\BasilParser\Exception\CircularStepImportException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownElementException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Provider\DataSet\DataSetProviderInterface;
use webignition\BasilParser\Provider\DataSet\EmptyDataSetProvider;
use webignition\BasilParser\Provider\DataSet\PopulatedDataSetProvider;
use webignition\BasilParser\Provider\Page\EmptyPageProvider;
use webignition\BasilParser\Provider\Page\Factory as PageProviderFactory;
use webignition\BasilParser\Provider\Page\PageProviderInterface;
use webignition\BasilParser\Provider\Page\PopulatedPageProvider;
use webignition\BasilParser\Provider\Step\EmptyStepProvider;
use webignition\BasilParser\Provider\Step\PopulatedStepProvider;
use webignition\BasilParser\Provider\Step\StepProviderInterface;
use webignition\BasilParser\Resolver\StepResolver;
use webignition\BasilParser\Tests\Services\FixturePathFinder;
use webignition\BasilParser\Tests\Services\TestIdentifierFactory;

class StepResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var StepResolver
     */
    private $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = StepResolver::createResolver();
    }

    /**
     * @dataProvider resolveIncludingPageElementReferencesForStepImport
     */
    public function testResolveIncludingPageElementReferencesForStepImport(
        StepInterface $step,
        StepProviderInterface $stepProvider,
        StepInterface $expectedStep
    ) {
        $resolvedStep = $this->resolver->resolveIncludingPageElementReferences(
            $step,
            $stepProvider,
            new EmptyDataSetProvider(),
            new EmptyPageProvider()
        );

        $this->assertEquals($expectedStep, $resolvedStep);
    }

    /**
     * @dataProvider resolveIncludingPageElementReferencesForStepImport
     */
    public function testResolveIncludingElementParameterReferencesForStepImport(
        StepInterface $step,
        StepProviderInterface $stepProvider,
        StepInterface $expectedStep
    ) {
        $resolvedStep = $this->resolver->resolveIncludingElementParameterReferences(
            $step,
            $stepProvider,
            new EmptyDataSetProvider(),
            new EmptyPageProvider()
        );

        $this->assertEquals($expectedStep, $resolvedStep);
    }

    public function resolveIncludingPageElementReferencesForStepImport(): array
    {
        return [
            'no step imports, empty step' => [
                'step' => new PendingImportResolutionStep(new Step([], []), 'step_import_name', ''),
                'stepProvider' => new PopulatedStepProvider([
                    'step_import_name' => new Step([], []),
                ]),
                'expectedStep' => new Step([], []),
            ],
            'no step imports, non-empty step' => [
                'step' => new PendingImportResolutionStep(new Step([], []), 'step_import_name', ''),
                'stepProvider' => new PopulatedStepProvider([
                    'step_import_name' => new Step([
                        new WaitAction('wait 1', LiteralValue::createStringValue('1')),
                    ], [
                        new Assertion('".selector" exists', null, null)
                    ]),
                ]),
                'expectedStep' => new Step([
                    new WaitAction('wait 1', LiteralValue::createStringValue('1')),
                ], [
                    new Assertion('".selector" exists', null, null)
                ]),
            ],
            'step with actions imports step with actions' => [
                'step' => new PendingImportResolutionStep(
                    new Step([
                        new WaitAction('wait 2', LiteralValue::createStringValue('2')),
                    ], []),
                    'step_import_name',
                    ''
                ),
                'stepProvider' => new PopulatedStepProvider([
                    'step_import_name' => new Step([
                        new WaitAction('wait 1', LiteralValue::createStringValue('1')),
                    ], [
                        new Assertion('".selector" exists', null, null)
                    ]),
                ]),
                'expectedStep' => new Step([
                    new WaitAction('wait 1', LiteralValue::createStringValue('1')),
                    new WaitAction('wait 2', LiteralValue::createStringValue('2')),
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
                        new WaitAction('wait 1', LiteralValue::createStringValue('1')),
                    ], [
                        new Assertion('".selector" exists', null, null)
                    ]),
                ]),
                'expectedStep' => new Step([
                    new WaitAction('wait 1', LiteralValue::createStringValue('1')),
                ], [
                    new Assertion('".selector" exists', null, null)
                ]),
            ],
        ];
    }

    /**
     * @dataProvider resolveDataProviderImportDataProvider
     */
    public function testResolveIncludingPageElementReferencesDataProviderImport(
        StepInterface $step,
        DataSetProviderInterface $dataSetProvider,
        StepInterface $expectedStep
    ) {
        $resolvedStep = $this->resolver->resolveIncludingPageElementReferences(
            $step,
            new EmptyStepProvider(),
            $dataSetProvider,
            new EmptyPageProvider()
        );

        $this->assertEquals($expectedStep, $resolvedStep);
    }

    /**
     * @dataProvider resolveDataProviderImportDataProvider
     */
    public function testResolveIncludingElementParameterReferencesDataProviderImport(
        StepInterface $step,
        DataSetProviderInterface $dataSetProvider,
        StepInterface $expectedStep
    ) {
        $resolvedStep = $this->resolver->resolveIncludingElementParameterReferences(
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
                    'data_provider_import_name' => new DataSetCollection([
                        new DataSet('0', [
                            'foo' => 'bar',
                        ])
                    ]),
                ]),
                'expectedStep' => (new Step([], []))->withDataSetCollection(new DataSetCollection([
                    new DataSet('0', [
                        'foo' => 'bar',
                    ])
                ])),
            ],
        ];
    }

    /**
     * @dataProvider resolveActionsNoResolvableReferencesDataProvider
     * @dataProvider resolveActionsWithResolvablePageElementReferencesDataProvider
     */
    public function testResolveIncludingPageElementReferencesForActions(
        StepInterface $step,
        PageProviderInterface $pageProvider,
        StepInterface $expectedStep
    ) {
        $resolvedStep = $this->resolver->resolveIncludingPageElementReferences(
            $step,
            new EmptyStepProvider(),
            new EmptyDataSetProvider(),
            $pageProvider
        );

        $this->assertEquals($expectedStep, $resolvedStep);
    }

    /**
     * @dataProvider resolveActionsNoResolvableReferencesDataProvider
     * @dataProvider resolveActionsWithResolvableElementParameterReferencesDataProvider
     */
    public function testResolveIncludingElementParameterReferencesForActions(
        StepInterface $step,
        PageProviderInterface $pageProvider,
        StepInterface $expectedStep
    ) {
        $resolvedStep = $this->resolver->resolveIncludingElementParameterReferences(
            $step,
            new EmptyStepProvider(),
            new EmptyDataSetProvider(),
            $pageProvider
        );

        $this->assertEquals($expectedStep, $resolvedStep);
    }

    public function resolveActionsNoResolvableReferencesDataProvider(): array
    {
        return [
            'no actions' => [
                'step' => new Step([], []),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => new Step([], []),
            ],
            'no resolvable actions' => [
                'step' => new Step([
                    new WaitAction('wait 30', LiteralValue::createStringValue('30')),
                ], []),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => new Step([
                    new WaitAction('wait 30', LiteralValue::createStringValue('30')),
                ], []),
            ],
        ];
    }

    public function resolveActionsWithResolvablePageElementReferencesDataProvider(): array
    {
        $namedCssElementIdentifier = TestIdentifierFactory::createCssElementIdentifier('.selector', 1, 'element_name');

        return [
            'resolvable page element references in actions' => [
                'step' => new Step([
                    new InputAction(
                        'set page_import_name.elements.element_name to "value"',
                        new Identifier(
                            IdentifierTypes::PAGE_ELEMENT_REFERENCE,
                            new ObjectValue(
                                ValueTypes::PAGE_ELEMENT_REFERENCE,
                                'page_import_name.elements.element_name',
                                'page_import_name',
                                'element_name'
                            )
                        ),
                        LiteralValue::createStringValue('value'),
                        'page_import_name.elements.element_name to "value"'
                    )
                ], []),
                'pageProvider' => new PopulatedPageProvider([
                    'page_import_name' => new Page(
                        new Uri('http://example.com/'),
                        new IdentifierCollection([
                            $namedCssElementIdentifier,
                        ])
                    )
                ]),
                'expectedStep' => new Step([
                    new InputAction(
                        'set page_import_name.elements.element_name to "value"',
                        $namedCssElementIdentifier,
                        LiteralValue::createStringValue('value'),
                        'page_import_name.elements.element_name to "value"'
                    )
                ], []),
            ],
        ];
    }

    public function resolveActionsWithResolvableElementParameterReferencesDataProvider(): array
    {
        $namedCssElementIdentifier = TestIdentifierFactory::createCssElementIdentifier('.selector', 1, 'element_name');

        return [
            'resolvable element parameters in actions' => [
                'step' => (new Step([
                    new InputAction(
                        'set $elements.element_name to "value"',
                        new Identifier(
                            IdentifierTypes::ELEMENT_PARAMETER,
                            new ObjectValue(
                                ValueTypes::ELEMENT_PARAMETER,
                                '$elements.element_name',
                                ObjectNames::ELEMENT,
                                'element_name'
                            )
                        ),
                        LiteralValue::createStringValue('value'),
                        '$elements.element_name to "value"'
                    )
                ], []))->withIdentifierCollection(new IdentifierCollection([
                    $namedCssElementIdentifier,
                ])),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => (new Step([
                    new InputAction(
                        'set $elements.element_name to "value"',
                        $namedCssElementIdentifier,
                        LiteralValue::createStringValue('value'),
                        '$elements.element_name to "value"'
                    )
                ], []))->withIdentifierCollection(new IdentifierCollection([
                    $namedCssElementIdentifier,
                ])),
            ],
        ];
    }

    /**
     * @dataProvider resolveAssertionsNoResolvableReferencesDataProvider
     * @dataProvider resolveAssertionsWithResolvablePageElementReferencesDataProvider
     */
    public function testResolveIncludingPageElementReferencesForAssertions(
        StepInterface $step,
        PageProviderInterface $pageProvider,
        StepInterface $expectedStep
    ) {
        $resolvedStep = $this->resolver->resolveIncludingPageElementReferences(
            $step,
            new EmptyStepProvider(),
            new EmptyDataSetProvider(),
            $pageProvider
        );

        $this->assertEquals($expectedStep, $resolvedStep);
    }

    /**
     * @dataProvider resolveAssertionsNoResolvableReferencesDataProvider
     * @dataProvider resolveAssertionsWithResolvableElementParameterReferencesDataProvider
     */
    public function testResolveIncludingElementParameterReferencesForAssertions(
        StepInterface $step,
        PageProviderInterface $pageProvider,
        StepInterface $expectedStep
    ) {
        $resolvedStep = $this->resolver->resolveIncludingElementParameterReferences(
            $step,
            new EmptyStepProvider(),
            new EmptyDataSetProvider(),
            $pageProvider
        );

        $this->assertEquals($expectedStep, $resolvedStep);
    }

    public function resolveAssertionsNoResolvableReferencesDataProvider(): array
    {
        $nonResolvableAssertion = new Assertion(
            '".selector" exists',
            new ElementValue(
                new ElementIdentifier(
                    LiteralValue::createCssSelectorValue('.selector')
                )
            ),
            AssertionComparisons::EXISTS
        );

        return [
            'no assertions' => [
                'step' => new Step([], []),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => new Step([], []),
            ],
            'no resolvable assertions' => [
                'step' => new Step([], [
                    $nonResolvableAssertion,
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => new Step([], [
                    $nonResolvableAssertion
                ]),
            ],
        ];
    }

    public function resolveAssertionsWithResolvablePageElementReferencesDataProvider(): array
    {
        $namedCssElementIdentifier = TestIdentifierFactory::createCssElementIdentifier('.selector', 1, 'element_name');

        return [
            'resolvable page element references in assertions' => [
                'step' => new Step([], [
                    new Assertion(
                        'page_import_name.elements.element_name exists',
                        new ObjectValue(
                            ValueTypes::PAGE_ELEMENT_REFERENCE,
                            'page_import_name.elements.element_name',
                            'page_import_name',
                            'element_name'
                        ),
                        AssertionComparisons::EXISTS
                    ),
                ]),
                'pageProvider' => new PopulatedPageProvider([
                    'page_import_name' => new Page(
                        new Uri('http://example.com/'),
                        new IdentifierCollection([
                            $namedCssElementIdentifier,
                        ])
                    )
                ]),
                'expectedStep' => new Step([], [
                    new Assertion(
                        'page_import_name.elements.element_name exists',
                        new ElementValue($namedCssElementIdentifier),
                        AssertionComparisons::EXISTS
                    ),
                ]),
            ],
        ];
    }

    public function resolveAssertionsWithResolvableElementParameterReferencesDataProvider(): array
    {
        $namedCssElementIdentifier = TestIdentifierFactory::createCssElementIdentifier('.selector', 1, 'element_name');

        return [
            'resolvable element parameter references in assertions' => [
                'step' => (new Step([], [
                    new Assertion(
                        '$elements.element_name exists',
                        new ObjectValue(
                            ValueTypes::ELEMENT_PARAMETER,
                            '$elements.element_name',
                            ObjectNames::ELEMENT,
                            'element_name'
                        ),
                        AssertionComparisons::EXISTS
                    ),
                ]))->withIdentifierCollection(new IdentifierCollection([
                    $namedCssElementIdentifier,
                ])),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => (new Step([], [
                    new Assertion(
                        '$elements.element_name exists',
                        new ElementValue($namedCssElementIdentifier),
                        AssertionComparisons::EXISTS
                    ),
                ]))->withIdentifierCollection(new IdentifierCollection([
                    $namedCssElementIdentifier,
                ])),
            ],
        ];
    }

    /**
     * @dataProvider resolveElementIdentifiersNoResolvableIdentifiersDataProvider
     * @dataProvider resolveElementIdentifiersWithPageElementReferencesDataProvider
     */
    public function testResolveIncludingPageElementReferencesForElementIdentifiers(
        StepInterface $step,
        PageProviderInterface $pageProvider,
        StepInterface $expectedStep
    ) {
        $resolvedStep = $this->resolver->resolveIncludingPageElementReferences(
            $step,
            new EmptyStepProvider(),
            new EmptyDataSetProvider(),
            $pageProvider
        );

        $this->assertEquals($expectedStep, $resolvedStep);
    }

    /**
     * @dataProvider resolveElementIdentifiersNoResolvableIdentifiersDataProvider
     */
    public function testResolveIncludingElementParameterReferencesForElementIdentifiers(
        StepInterface $step,
        PageProviderInterface $pageProvider,
        StepInterface $expectedStep
    ) {
        $resolvedStep = $this->resolver->resolveIncludingElementParameterReferences(
            $step,
            new EmptyStepProvider(),
            new EmptyDataSetProvider(),
            $pageProvider
        );

        $this->assertEquals($expectedStep, $resolvedStep);
    }

    public function resolveElementIdentifiersNoResolvableIdentifiersDataProvider(): array
    {
        return [
            'no element identifiers' => [
                'step' => new Step([], []),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => new Step([], []),
            ],
            'no resolvable element identifiers' => [
                'step' => (new Step([], []))->withIdentifierCollection(new IdentifierCollection([
                    TestIdentifierFactory::createCssElementIdentifier('.selector'),
                ])),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => (new Step([], []))->withIdentifierCollection(new IdentifierCollection([
                    TestIdentifierFactory::createCssElementIdentifier('.selector'),
                ]))
            ],
        ];
    }

    public function resolveElementIdentifiersWithPageElementReferencesDataProvider(): array
    {
        return [
            'resolvable element identifiers: page element references' => [
                'step' => (new Step([], []))->withIdentifierCollection(new IdentifierCollection([
                    TestIdentifierFactory::createPageElementReferenceIdentifier(
                        new ObjectValue(
                            ValueTypes::PAGE_ELEMENT_REFERENCE,
                            'page_import_name.elements.element_name',
                            'page_import_name',
                            'element_name'
                        ),
                        'element_name'
                    ),
                ])),
                'pageProvider' => new PopulatedPageProvider([
                    'page_import_name' => new Page(
                        new Uri('http://example.com/'),
                        new IdentifierCollection([
                            TestIdentifierFactory::createCssElementIdentifier('.selector', 1, 'element_name'),
                        ])
                    )
                ]),
                'expectedStep' => (new Step([], []))->withIdentifierCollection(new IdentifierCollection([
                    TestIdentifierFactory::createCssElementIdentifier('.selector', 1, 'element_name'),
                ]))
            ],
        ];
    }

    /**
     * @dataProvider resolveCircularReferenceDataProvider
     */
    public function testResolveIncludingPageElementReferencesForCircularReference(
        StepInterface $step,
        StepProviderInterface $stepProvider,
        string $expectedCircularImportName
    ) {
        try {
            $this->resolver->resolveIncludingPageElementReferences(
                $step,
                $stepProvider,
                new EmptyDataSetProvider(),
                new EmptyPageProvider()
            );

            $this->fail('CircularStepImportException not thrown for import "' . $expectedCircularImportName . '"');
        } catch (CircularStepImportException $circularStepImportException) {
            $this->assertSame($expectedCircularImportName, $circularStepImportException->getImportName());
        }
    }

    /**
     * @dataProvider resolveCircularReferenceDataProvider
     */
    public function testResolveIncludingElementParameterReferencesForCircularReference(
        StepInterface $step,
        StepProviderInterface $stepProvider,
        string $expectedCircularImportName
    ) {
        try {
            $this->resolver->resolveIncludingElementParameterReferences(
                $step,
                $stepProvider,
                new EmptyDataSetProvider(),
                new EmptyPageProvider()
            );

            $this->fail('CircularStepImportException not thrown for import "' . $expectedCircularImportName . '"');
        } catch (CircularStepImportException $circularStepImportException) {
            $this->assertSame($expectedCircularImportName, $circularStepImportException->getImportName());
        }
    }

    public function resolveCircularReferenceDataProvider(): array
    {
        return [
            'direct self-circular reference' => [
                'step' => new PendingImportResolutionStep(
                    new Step([], []),
                    'start',
                    ''
                ),
                'stepProvider' => new PopulatedStepProvider([
                    'start' => new PendingImportResolutionStep(
                        new Step([], []),
                        'start',
                        ''
                    ),
                ]),
                'expectedCircularImportName' => 'start',
            ],
            'indirect self-circular reference' => [
                'step' => new PendingImportResolutionStep(
                    new Step([], []),
                    'start',
                    ''
                ),
                'stepProvider' => new PopulatedStepProvider([
                    'start' => new PendingImportResolutionStep(
                        new Step([], []),
                        'middle',
                        ''
                    ),
                    'middle' => new PendingImportResolutionStep(
                        new Step([], []),
                        'start',
                        ''
                    ),
                ]),
                'expectedCircularImportName' => 'start',
            ],
            'indirect circular reference' => [
                'step' => new PendingImportResolutionStep(
                    new Step([], []),
                    'one',
                    ''
                ),
                'stepProvider' => new PopulatedStepProvider([
                    'one' => new PendingImportResolutionStep(
                        new Step([], []),
                        'two',
                        ''
                    ),
                    'two' => new PendingImportResolutionStep(
                        new Step([], []),
                        'three',
                        ''
                    ),
                    'three' => new PendingImportResolutionStep(
                        new Step([], []),
                        'two',
                        ''
                    ),
                ]),
                'expectedCircularImportName' => 'two',
            ],
        ];
    }

    /**
     * @dataProvider resolveIncludingPageElementReferencesThrowsExceptionDataProvider
     */
    public function testResolveIncludingPageElementReferencesThrowsException(
        StepInterface $step,
        PageProviderInterface $pageProvider,
        string $expectedException,
        string $expectedExceptionMessage,
        ExceptionContextInterface $expectedExceptionContext
    ) {
        try {
            $this->resolver->resolveIncludingPageElementReferences(
                $step,
                new EmptyStepProvider(),
                new EmptyDataSetProvider(),
                $pageProvider
            );

            $this->fail('Exception "' . $expectedException . '" not thrown');
        } catch (\Exception $exception) {
            $this->assertInstanceOf($expectedException, $exception);
            $this->assertSame($expectedExceptionMessage, $exception->getMessage());

            if ($exception instanceof ContextAwareExceptionInterface) {
                $this->assertEquals($expectedExceptionContext, $exception->getExceptionContext());
            }
        }
    }

    public function resolveIncludingPageElementReferencesThrowsExceptionDataProvider(): array
    {
        $invalidYamlPath = FixturePathFinder::find('invalid-yaml.yml');

        return [
            'InvalidPageElementIdentifierException: action has page element reference, referenced page invalid' => [
                'step' => new Step([
                    new InteractionAction(
                        'click page_import_name.elements.element_name',
                        ActionTypes::CLICK,
                        new Identifier(
                            IdentifierTypes::PAGE_ELEMENT_REFERENCE,
                            new ObjectValue(
                                ValueTypes::PAGE_ELEMENT_REFERENCE,
                                'page_import_name.elements.element_name',
                                'page_import_name',
                                'element_name'
                            )
                        ),
                        'page_import_name.elements.element_name'
                    )
                ], []),
                'pageProvider' => PageProviderFactory::createFactory()->createDeferredPageProvider([
                    'page_import_name' => FixturePathFinder::find('Page/example.com.non-elemental-identifier.yml'),
                ]),
                'expectedException' => InvalidPageElementIdentifierException::class,
                'expectedExceptionMessage' => 'Invalid page element identifier "".selector".attribute_name"',
                'expectedExceptionContext' => new ExceptionContext([
                    ExceptionContextInterface::KEY_CONTENT => 'click page_import_name.elements.element_name',
                ]),
            ],
            'InvalidPageElementIdentifierException: assertion has page element reference, referenced page invalid' => [
                'step' => new Step([], [
                    new Assertion(
                        'page_import_name.elements.element_name exists',
                        new ObjectValue(
                            ValueTypes::PAGE_ELEMENT_REFERENCE,
                            'page_import_name.elements.element_name',
                            'page_import_name',
                            'element_name'
                        ),
                        AssertionComparisons::EXISTS
                    )
                ]),
                'pageProvider' => PageProviderFactory::createFactory()->createDeferredPageProvider([
                    'page_import_name' => FixturePathFinder::find('Page/example.com.non-elemental-identifier.yml'),
                ]),
                'expectedException' => InvalidPageElementIdentifierException::class,
                'expectedExceptionMessage' => 'Invalid page element identifier "".selector".attribute_name"',
                'expectedExceptionContext' => new ExceptionContext([
                    ExceptionContextInterface::KEY_CONTENT => 'page_import_name.elements.element_name exists',
                ]),
            ],
            'NonRetrievablePageException: action has page element reference, referenced page invalid' => [
                'step' => new Step([
                    new InteractionAction(
                        'click page_import_name.elements.element_name',
                        ActionTypes::CLICK,
                        new Identifier(
                            IdentifierTypes::PAGE_ELEMENT_REFERENCE,
                            new ObjectValue(
                                ValueTypes::PAGE_ELEMENT_REFERENCE,
                                'page_import_name.elements.element_name',
                                'page_import_name',
                                'element_name'
                            )
                        ),
                        'page_import_name.elements.element_name'
                    )
                ], []),
                'pageProvider' => PageProviderFactory::createFactory()->createDeferredPageProvider([
                    'page_import_name' => $invalidYamlPath,
                ]),
                'expectedException' => NonRetrievablePageException::class,
                'expectedExceptionMessage' => 'Cannot retrieve page "page_import_name" from "' . $invalidYamlPath . '"',
                'expectedExceptionContext' => new ExceptionContext([
                    ExceptionContextInterface::KEY_CONTENT => 'click page_import_name.elements.element_name',
                ]),
            ],
            'NonRetrievablePageException: assertion has page element reference, referenced page invalid' => [
                'step' => new Step([], [
                    new Assertion(
                        'page_import_name.elements.element_name exists',
                        new ObjectValue(
                            ValueTypes::PAGE_ELEMENT_REFERENCE,
                            'page_import_name.elements.element_name',
                            'page_import_name',
                            'element_name'
                        ),
                        AssertionComparisons::EXISTS
                    )
                ]),
                'pageProvider' => PageProviderFactory::createFactory()->createDeferredPageProvider([
                    'page_import_name' => $invalidYamlPath,
                ]),
                'expectedException' => NonRetrievablePageException::class,
                'expectedExceptionMessage' => 'Cannot retrieve page "page_import_name" from "' . $invalidYamlPath . '"',
                'expectedExceptionContext' => new ExceptionContext([
                    ExceptionContextInterface::KEY_CONTENT => 'page_import_name.elements.element_name exists',
                ]),
            ],
            'UnknownPageElementException: action has page element reference, referenced page lacks element' => [
                'step' => new Step([
                    new InteractionAction(
                        'click page_import_name.elements.element_name',
                        ActionTypes::CLICK,
                        new Identifier(
                            IdentifierTypes::PAGE_ELEMENT_REFERENCE,
                            new ObjectValue(
                                ValueTypes::PAGE_ELEMENT_REFERENCE,
                                'page_import_name.elements.element_name',
                                'page_import_name',
                                'element_name'
                            )
                        ),
                        'page_import_name.elements.element_name'
                    )
                ], []),
                'pageProvider' => PageProviderFactory::createFactory()->createDeferredPageProvider([
                    'page_import_name' => FixturePathFinder::find('Page/example.com.heading.yml'),
                ]),
                'expectedException' => UnknownPageElementException::class,
                'expectedExceptionMessage' => 'Unknown page element "element_name" in page "page_import_name"',
                'expectedExceptionContext' => new ExceptionContext([
                    ExceptionContextInterface::KEY_CONTENT => 'click page_import_name.elements.element_name',
                ]),
            ],
            'UnknownPageElementException: assertion has page element reference, referenced page lacks element' => [
                'step' => new Step([], [
                    new Assertion(
                        'page_import_name.elements.element_name exists',
                        new ObjectValue(
                            ValueTypes::PAGE_ELEMENT_REFERENCE,
                            'page_import_name.elements.element_name',
                            'page_import_name',
                            'element_name'
                        ),
                        AssertionComparisons::EXISTS
                    )
                ]),
                'pageProvider' => PageProviderFactory::createFactory()->createDeferredPageProvider([
                    'page_import_name' => FixturePathFinder::find('Page/example.com.heading.yml'),
                ]),
                'expectedException' => UnknownPageElementException::class,
                'expectedExceptionMessage' => 'Unknown page element "element_name" in page "page_import_name"',
                'expectedExceptionContext' => new ExceptionContext([
                    ExceptionContextInterface::KEY_CONTENT => 'page_import_name.elements.element_name exists',
                ]),
            ],
            'UnknownPageException: action has page element reference, page does not exist' => [
                'step' => new Step([
                    new InteractionAction(
                        'click page_import_name.elements.element_name',
                        ActionTypes::CLICK,
                        new Identifier(
                            IdentifierTypes::PAGE_ELEMENT_REFERENCE,
                            new ObjectValue(
                                ValueTypes::PAGE_ELEMENT_REFERENCE,
                                'page_import_name.elements.element_name',
                                'page_import_name',
                                'element_name'
                            )
                        ),
                        'page_import_name.elements.element_name'
                    )
                ], []),
                'pageProvider' => new EmptyPageProvider(),
                'expectedException' => UnknownPageException::class,
                'expectedExceptionMessage' => 'Unknown page "page_import_name"',
                'expectedExceptionContext' => new ExceptionContext([
                    ExceptionContextInterface::KEY_CONTENT => 'click page_import_name.elements.element_name',
                ]),
            ],
            'UnknownPageException: assertion has page element reference, page does not exist' => [
                'step' => new Step([], [
                    new Assertion(
                        'page_import_name.elements.element_name exists',
                        new ObjectValue(
                            ValueTypes::PAGE_ELEMENT_REFERENCE,
                            'page_import_name.elements.element_name',
                            'page_import_name',
                            'element_name'
                        ),
                        AssertionComparisons::EXISTS
                    )
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'expectedException' => UnknownPageException::class,
                'expectedExceptionMessage' => 'Unknown page "page_import_name"',
                'expectedExceptionContext' => new ExceptionContext([
                    ExceptionContextInterface::KEY_CONTENT => 'page_import_name.elements.element_name exists',
                ]),
            ],
        ];
    }

    /**
     * @dataProvider resolveIncludingElementParameterReferencesThrowsExceptionDataProvider
     */
    public function testResolveIncludingElementParameterReferencesThrowsException(
        StepInterface $step,
        string $expectedException,
        string $expectedExceptionMessage,
        ExceptionContextInterface $expectedExceptionContext
    ) {
        try {
            $this->resolver->resolveIncludingElementParameterReferences(
                $step,
                new EmptyStepProvider(),
                new EmptyDataSetProvider(),
                new EmptyPageProvider()
            );

            $this->fail('Exception "' . $expectedException . '" not thrown');
        } catch (\Exception $exception) {
            $this->assertInstanceOf($expectedException, $exception);
            $this->assertSame($expectedExceptionMessage, $exception->getMessage());

            if ($exception instanceof ContextAwareExceptionInterface) {
                $this->assertEquals($expectedExceptionContext, $exception->getExceptionContext());
            }
        }
    }

    public function resolveIncludingElementParameterReferencesThrowsExceptionDataProvider(): array
    {
        return [
            'UnknownElementException: action has element parameter reference, element missing' => [
                'step' => new Step([
                    new InteractionAction(
                        'click $elements.element_name',
                        ActionTypes::CLICK,
                        new Identifier(
                            IdentifierTypes::ELEMENT_PARAMETER,
                            new ObjectValue(
                                ValueTypes::ELEMENT_PARAMETER,
                                '$elements.element_name',
                                ObjectNames::ELEMENT,
                                'element_name'
                            )
                        ),
                        '$elements.element_name'
                    )
                ], []),
                'expectedException' => UnknownElementException::class,
                'expectedExceptionMessage' => 'Unknown element "element_name"',
                'expectedExceptionContext' => new ExceptionContext([
                    ExceptionContextInterface::KEY_CONTENT => 'click $elements.element_name',
                ]),
            ],
            'UnknownElementException: assertion has page element reference, referenced page invalid' => [
                'step' => new Step([], [
                    new Assertion(
                        '$elements.element_name exists',
                        new ObjectValue(
                            ValueTypes::ELEMENT_PARAMETER,
                            '$elements.element_name',
                            ObjectNames::ELEMENT,
                            'element_name'
                        ),
                        AssertionComparisons::EXISTS
                    )
                ]),
                'expectedException' => UnknownElementException::class,
                'expectedExceptionMessage' => 'Unknown element "element_name"',
                'expectedExceptionContext' => new ExceptionContext([
                    ExceptionContextInterface::KEY_CONTENT => '$elements.element_name exists',
                ]),
            ],
        ];
    }
}
