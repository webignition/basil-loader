<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Resolver;

use webignition\BasilModel\DataSet\DataSet;
use webignition\BasilModel\DataSet\DataSetCollection;
use webignition\BasilModel\Step\PendingImportResolutionStep;
use webignition\BasilModel\Step\Step;
use webignition\BasilModel\Step\StepInterface;
use webignition\BasilModelFactory\Action\ActionFactory;
use webignition\BasilModelFactory\AssertionFactory;
use webignition\BasilParser\Exception\CircularStepImportException;
use webignition\BasilParser\Provider\DataSet\DataSetProviderInterface;
use webignition\BasilParser\Provider\DataSet\EmptyDataSetProvider;
use webignition\BasilParser\Provider\DataSet\DataSetProvider;
use webignition\BasilParser\Provider\Step\PopulatedStepProvider;
use webignition\BasilParser\Provider\Step\StepProviderInterface;
use webignition\BasilParser\Resolver\StepImportResolver;

class StepImportResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var StepImportResolver
     */
    private $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = StepImportResolver::createResolver();
    }

    /**
     * @dataProvider resolveStepImportDataProvider
     */
    public function testResolveStepImport(
        StepInterface $step,
        StepProviderInterface $stepProvider,
        StepInterface $expectedStep
    ) {
        $resolvedStep = $this->resolver->resolveStepImport($step, $stepProvider);

        $this->assertEquals($expectedStep, $resolvedStep);
    }

    public function resolveStepImportDataProvider(): array
    {
        $actionFactory = ActionFactory::createFactory();
        $assertionFactory = AssertionFactory::createFactory();

        $nonResolvableActions = [
            $actionFactory->createFromActionString('wait 1'),
        ];

        $nonResolvableAssertions = [
            $assertionFactory->createFromAssertionString('".selector" exists'),
        ];

        $resolvableActions = [
            $actionFactory->createFromActionString('click page_import_name.elements.element_name'),
            $actionFactory->createFromActionString('click $elements.element_name'),
            $actionFactory->createFromActionString('click $elements.element_name.attribute_name'),
            $actionFactory->createFromActionString(
                'set page_import_name.elements.element_name to page_import_name.elements.element_name'
            ),
            $actionFactory->createFromActionString(
                'set $elements.element_name to $elements.element_name'
            ),
            $actionFactory->createFromActionString(
                'set $elements.element_name.attribute_name to $elements.element_name.attribute_name'
            ),
        ];

        $resolvableAssertions = [
            $assertionFactory->createFromAssertionString('page_import_name.elements.element_name exists'),
            $assertionFactory->createFromAssertionString('$elements.element_name exists'),
            $assertionFactory->createFromAssertionString('$elements.element_name.attribute_name exists'),
        ];

        return [
            'empty step, no imports' => [
                'step' => new PendingImportResolutionStep(new Step([], []), '', ''),
                'stepProvider' => new PopulatedStepProvider([
                    'step_import_name' => new Step([], []),
                ]),
                'expectedStep' => new Step([], []),
            ],
            'empty step imports empty step' => [
                'step' => new PendingImportResolutionStep(new Step([], []), 'step_import_name', ''),
                'stepProvider' => new PopulatedStepProvider([
                    'step_import_name' => new Step([], []),
                ]),
                'expectedStep' => new Step([], []),
            ],
            'empty step imports non-empty step, non-resolvable actions and assertions' => [
                'step' => new PendingImportResolutionStep(new Step([], []), 'step_import_name', ''),
                'stepProvider' => new PopulatedStepProvider([
                    'step_import_name' => new Step($nonResolvableActions, $nonResolvableAssertions),
                ]),
                'expectedStep' => new Step($nonResolvableActions, $nonResolvableAssertions),
            ],
            'empty step imports non-empty step, resolvable actions and assertions' => [
                'step' => new PendingImportResolutionStep(new Step([], []), 'step_import_name', ''),
                'stepProvider' => new PopulatedStepProvider([
                    'step_import_name' => new Step($resolvableActions, $resolvableAssertions),
                ]),
                'expectedStep' => new Step($resolvableActions, $resolvableAssertions),
            ],
            'step with actions and assertions imports step with actions and assertions' => [
                'step' => new PendingImportResolutionStep(
                    new Step($resolvableActions, $resolvableAssertions),
                    'step_import_name',
                    ''
                ),
                'stepProvider' => new PopulatedStepProvider([
                    'step_import_name' => new Step($nonResolvableActions, $nonResolvableAssertions),
                ]),
                'expectedStep' => new Step(
                    array_merge($nonResolvableActions, $resolvableActions),
                    array_merge($nonResolvableAssertions, $resolvableAssertions)
                ),
            ],
            'deferred' => [
                'step' => new PendingImportResolutionStep(new Step([], []), 'deferred_step_import_name', ''),
                'stepProvider' => new PopulatedStepProvider([
                    'deferred_step_import_name' => new PendingImportResolutionStep(
                        new Step([], []),
                        'step_import_name',
                        ''
                    ),
                    'step_import_name' => new Step($nonResolvableActions, $nonResolvableAssertions),
                ]),
                'expectedStep' => new Step($nonResolvableActions, $nonResolvableAssertions),
            ],
            'empty step imports actions and assertions, has data provider import name' => [
                'step' => new PendingImportResolutionStep(
                    new Step([], []),
                    'step_import_name',
                    'data_provider_import_name'
                ),
                'stepProvider' => new PopulatedStepProvider([
                    'step_import_name' => new Step($nonResolvableActions, $nonResolvableAssertions),
                ]),
                'expectedStep' => new PendingImportResolutionStep(
                    new Step($nonResolvableActions, $nonResolvableAssertions),
                    '',
                    'data_provider_import_name'
                ),
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
        $resolvedStep = $this->resolver->resolveDataProviderImport(
            $step,
            $dataSetProvider
        );

        $this->assertEquals($expectedStep, $resolvedStep);
    }

    public function resolveDataProviderImportDataProvider(): array
    {
        return [
            'non-pending step' => [
                'step' => new Step([], []),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedStep' => new Step([], []),
            ],
            'empty data provider name' => [
                'step' => new PendingImportResolutionStep(new Step([], []), '', ''),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedStep' => new Step([], []),
            ],
            'has data provider name, empty step import name' => [
                'step' => new PendingImportResolutionStep(
                    new Step([], []),
                    '',
                    'data_provider_import_name'
                ),
                'dataSetProvider' => new DataSetProvider([
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
            'has data provider name, has step import name' => [
                'step' => new PendingImportResolutionStep(
                    new Step([], []),
                    'step_import_name',
                    'data_provider_import_name'
                ),
                'dataSetProvider' => new DataSetProvider([
                    'data_provider_import_name' => new DataSetCollection([
                        new DataSet('0', [
                            'foo' => 'bar',
                        ])
                    ]),
                ]),
                'expectedStep' => (new PendingImportResolutionStep(
                    new Step([], []),
                    'step_import_name',
                    ''
                ))->withDataSetCollection(new DataSetCollection([
                    new DataSet('0', [
                        'foo' => 'bar',
                    ])
                ])),
            ],
        ];
    }

    /**
     * @dataProvider resolveStepImportThrowsCircularReferenceExceptionDataProvider
     */
    public function testResolveStepImportThrowsCircularReferenceException(
        StepInterface $step,
        StepProviderInterface $stepProvider,
        string $expectedCircularImportName
    ) {
        try {
            $this->resolver->resolveStepImport($step, $stepProvider);

            $this->fail('CircularStepImportException not thrown for import "' . $expectedCircularImportName . '"');
        } catch (CircularStepImportException $circularStepImportException) {
            $this->assertSame($expectedCircularImportName, $circularStepImportException->getImportName());
        }
    }

    public function resolveStepImportThrowsCircularReferenceExceptionDataProvider(): array
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
}
