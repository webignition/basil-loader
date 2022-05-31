<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Tests\Unit\Resolver;

use webignition\BasilLoader\Resolver\CircularStepImportException;
use webignition\BasilLoader\Resolver\StepImportResolver;
use webignition\BasilModels\Model\DataSet\DataSetCollection;
use webignition\BasilModels\Model\Step\Step;
use webignition\BasilModels\Model\Step\StepInterface;
use webignition\BasilModels\Provider\DataSet\DataSetProvider;
use webignition\BasilModels\Provider\DataSet\DataSetProviderInterface;
use webignition\BasilModels\Provider\DataSet\EmptyDataSetProvider;
use webignition\BasilModels\Provider\Step\EmptyStepProvider;
use webignition\BasilModels\Provider\Step\StepProvider;
use webignition\BasilModels\Provider\Step\StepProviderInterface;
use webignition\BasilParser\ActionParser;
use webignition\BasilParser\AssertionParser;
use webignition\BasilParser\StepParser;

class StepImportResolverTest extends \PHPUnit\Framework\TestCase
{
    private StepImportResolver $resolver;

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
    ): void {
        $resolvedStep = $this->resolver->resolveStepImport($step, $stepProvider);

        $this->assertEquals($expectedStep, $resolvedStep);
    }

    /**
     * @return array<mixed>
     */
    public function resolveStepImportDataProvider(): array
    {
        $stepParser = StepParser::create();
        $actionParser = ActionParser::create();
        $assertionParser = AssertionParser::create();

        $nonResolvableActions = [
            $actionParser->parse('wait 1'),
        ];

        $nonResolvableAssertions = [
            $assertionParser->parse('$".selector" exists'),
        ];

        $resolvableActions = [
            $actionParser->parse('click $page_import_name.elements.element_name'),
            $actionParser->parse('click $elements.element_name'),
            $actionParser->parse(
                'set $page_import_name.elements.element_name to $page_import_name.elements.element_name'
            ),
            $actionParser->parse(
                'set $elements.element_name to $elements.element_name'
            ),
        ];

        $resolvableAssertions = [
            $assertionParser->parse('$page_import_name.elements.element_name exists'),
            $assertionParser->parse('$elements.element_name exists'),
            $assertionParser->parse('$elements.element_name.attribute_name exists'),
        ];

        return [
            'empty step, no imports' => [
                'step' => $stepParser->parse([]),
                'stepProvider' => new EmptyStepProvider(),
                'expectedStep' => new Step([], []),
            ],
            'empty step imports empty step' => [
                'step' => $stepParser->parse([
                    'use' => 'step_import_name',
                ]),
                'stepProvider' => new StepProvider([
                    'step_import_name' => $stepParser->parse([]),
                ]),
                'expectedStep' => new Step([], []),
            ],
            'empty step imports non-empty step, non-resolvable actions and assertions' => [
                'step' => $stepParser->parse([
                    'use' => 'step_import_name',
                ]),
                'stepProvider' => new StepProvider([
                    'step_import_name' => new Step($nonResolvableActions, $nonResolvableAssertions),
                ]),
                'expectedStep' => new Step($nonResolvableActions, $nonResolvableAssertions),
            ],
            'empty step imports non-empty step, resolvable actions and assertions' => [
                'step' => $stepParser->parse([
                    'use' => 'step_import_name',
                ]),
                'stepProvider' => new StepProvider([
                    'step_import_name' => new Step($resolvableActions, $resolvableAssertions),
                ]),
                'expectedStep' => new Step($resolvableActions, $resolvableAssertions),
            ],
            'step with actions and assertions imports step with actions and assertions' => [
                'step' => (new Step($resolvableActions, $resolvableAssertions))
                    ->withImportName('step_import_name'),
                'stepProvider' => new StepProvider([
                    'step_import_name' => new Step($nonResolvableActions, $nonResolvableAssertions),
                ]),
                'expectedStep' => new Step(
                    array_merge($nonResolvableActions, $resolvableActions),
                    array_merge($nonResolvableAssertions, $resolvableAssertions)
                ),
            ],
            'deferred' => [
                'step' => (new Step([], []))
                    ->withImportName('deferred_step_import_name'),
                'stepProvider' => new StepProvider([
                    'deferred_step_import_name' => (new Step([], []))
                        ->withImportName('step_import_name'),
                    'step_import_name' => new Step($nonResolvableActions, $nonResolvableAssertions),
                ]),
                'expectedStep' => new Step($nonResolvableActions, $nonResolvableAssertions),
            ],
            'empty step imports actions and assertions, has data provider import name' => [
                'step' => (new Step([], []))
                    ->withImportName('step_import_name')
                    ->withDataImportName('data_provider_import_name'),
                'stepProvider' => new StepProvider([
                    'step_import_name' => new Step($nonResolvableActions, $nonResolvableAssertions),
                ]),
                'expectedStep' => (new Step($nonResolvableActions, $nonResolvableAssertions))
                    ->withDataImportName('data_provider_import_name'),
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
    ): void {
        $resolvedStep = $this->resolver->resolveDataProviderImport(
            $step,
            $dataSetProvider
        );

        $this->assertEquals($expectedStep, $resolvedStep);
    }

    /**
     * @return array<mixed>
     */
    public function resolveDataProviderImportDataProvider(): array
    {
        return [
            'non-pending step' => [
                'step' => new Step([], []),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedStep' => new Step([], []),
            ],
            'has data provider name, empty step import name' => [
                'step' => (new Step([], []))
                    ->withDataImportName('data_provider_import_name'),
                'dataSetProvider' => new DataSetProvider([
                    'data_provider_import_name' => new DataSetCollection([
                        '0' => [
                            'foo' => 'bar',
                        ],
                    ]),
                ]),
                'expectedStep' => (new Step([], []))->withData(new DataSetCollection([
                    '0' => [
                        'foo' => 'bar',
                    ],
                ])),
            ],
            'has data provider name, has step import name' => [
                'step' => (new Step([], []))
                    ->withImportName('step_import_name')
                    ->withDataImportName('data_provider_import_name'),
                'dataSetProvider' => new DataSetProvider([
                    'data_provider_import_name' => new DataSetCollection([
                        '0' => [
                            'foo' => 'bar',
                        ]
                    ]),
                ]),
                'expectedStep' => (new Step([], []))
                    ->withImportName('step_import_name')
                    ->withData(new DataSetCollection([
                        '0' => [
                            'foo' => 'bar',
                        ]
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
    ): void {
        try {
            $this->resolver->resolveStepImport($step, $stepProvider);

            $this->fail('CircularStepImportException not thrown for import "' . $expectedCircularImportName . '"');
        } catch (CircularStepImportException $circularStepImportException) {
            $this->assertSame($expectedCircularImportName, $circularStepImportException->getImportName());
        }
    }

    /**
     * @return array<mixed>
     */
    public function resolveStepImportThrowsCircularReferenceExceptionDataProvider(): array
    {
        return [
            'direct self-circular reference' => [
                'step' => (new Step([], []))
                    ->withImportName('start'),
                'stepProvider' => new StepProvider([
                    'start' => (new Step([], []))
                        ->withImportName('start'),
                ]),
                'expectedCircularImportName' => 'start',
            ],
            'indirect self-circular reference' => [
                'step' => (new Step([], []))
                    ->withImportName('start'),
                'stepProvider' => new StepProvider([
                    'start' => (new Step([], []))
                        ->withImportName('middle'),
                    'middle' => (new Step([], []))
                        ->withImportName('start'),
                ]),
                'expectedCircularImportName' => 'start',
            ],
            'indirect circular reference' => [
                'step' => (new Step([], []))
                    ->withImportName('one'),
                'stepProvider' => new StepProvider([
                    'one' => (new Step([], []))
                        ->withImportName('two'),
                    'two' => (new Step([], []))
                        ->withImportName('three'),
                    'three' => (new Step([], []))
                        ->withImportName('two'),
                ]),
                'expectedCircularImportName' => 'two',
            ],
        ];
    }
}
