<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Factory;

use webignition\BasilModel\Action\ActionTypes;
use webignition\BasilModel\Action\InputAction;
use webignition\BasilModel\Action\InteractionAction;
use webignition\BasilModel\Assertion\Assertion;
use webignition\BasilModel\Assertion\AssertionComparisons;
use webignition\BasilModel\DataSet\DataSet;
use webignition\BasilModel\Identifier\Identifier;
use webignition\BasilModel\Identifier\IdentifierTypes;
use webignition\BasilModel\Step\PendingImportResolutionStep;
use webignition\BasilModel\Step\Step;
use webignition\BasilModel\Step\StepInterface;
use webignition\BasilModel\Value\Value;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilParser\DataStructure\Step as StepData;
use webignition\BasilParser\Factory\StepFactory;
use webignition\BasilParser\Tests\Services\StepFactoryFactory;

class StepFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var StepFactory
     */
    private $stepFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stepFactory = StepFactoryFactory::create();
    }

    /**
     * @dataProvider createFromStepDataDataProvider
     */
    public function testCreateFromStepData(StepData $stepData, StepInterface $expectedStep)
    {
        $step = $this->stepFactory->createFromStepData($stepData);

        $this->assertEquals($expectedStep, $step);
    }

    public function createFromStepDataDataProvider(): array
    {
        return [
            'empty step data' => [
                'stepData' => new StepData([]),
                'expectedStep' => new Step([], []),
            ],
            'empty actions and empty assertions' => [
                'stepData' => new StepData([
                    StepData::KEY_ACTIONS => [
                        '',
                        ' ',
                    ],
                    StepData::KEY_ASSERTIONS => [
                        '',
                        ' ',
                    ],
                ]),
                'expectedStep' => new Step([], []),
            ],
            'actions only' => [
                'stepData' => new StepData([
                    StepData::KEY_ACTIONS => [
                        'click ".selector"',
                        'set ".input" to "value"',
                    ],
                ]),
                'expectedStep' => new Step(
                    [
                        new InteractionAction(
                            ActionTypes::CLICK,
                            new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                new Value(
                                    ValueTypes::STRING,
                                    '.selector'
                                )
                            ),
                            '".selector"'
                        ),
                        new InputAction(
                            new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                new Value(
                                    ValueTypes::STRING,
                                    '.input'
                                )
                            ),
                            new Value(
                                ValueTypes::STRING,
                                'value'
                            ),
                            '".input" to "value"'
                        )
                    ],
                    []
                ),
            ],
            'assertions only' => [
                'stepData' => new StepData([
                    StepData::KEY_ASSERTIONS => [
                        '".selector" is "value"',
                        '".input" exists'
                    ],
                ]),
                'expectedStep' => new Step(
                    [
                    ],
                    [
                        new Assertion(
                            '".selector" is "value"',
                            new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                new Value(
                                    ValueTypes::STRING,
                                    '.selector'
                                )
                            ),
                            AssertionComparisons::IS,
                            new Value(
                                ValueTypes::STRING,
                                'value'
                            )
                        ),
                        new Assertion(
                            '".input" exists',
                            new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                new Value(
                                    ValueTypes::STRING,
                                    '.input'
                                )
                            ),
                            AssertionComparisons::EXISTS
                        ),
                    ]
                ),
            ],
            'page model element references' => [
                'stepData' => new StepData([
                    StepData::KEY_ACTIONS => [
                        'click page_import_name.elements.element_name'
                    ],
                    StepData::KEY_ASSERTIONS => [
                        'page_import_name.elements.element_name exists'
                    ],
                ]),
                'expectedStep' => new Step(
                    [
                        new InteractionAction(
                            ActionTypes::CLICK,
                            new Identifier(
                                IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                                new Value(
                                    ValueTypes::STRING,
                                    'page_import_name.elements.element_name'
                                )
                            ),
                            'page_import_name.elements.element_name'
                        )
                    ],
                    [
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
                    ]
                ),
            ],
            'import name only' => [
                'stepData' => new StepData([
                    StepData::KEY_USE => 'import_name',
                ]),
                'expectedStep' => new PendingImportResolutionStep([], [], 'import_name', ''),
            ],
            'data provider name only' => [
                'stepData' => new StepData([
                    StepData::KEY_DATA => 'data_provider_import_name',
                ]),
                'expectedStep' => new PendingImportResolutionStep([], [], '', 'data_provider_import_name'),
            ],
            'import name and data provider name' => [
                'stepData' => new StepData([
                    StepData::KEY_USE => 'import_name',
                    StepData::KEY_DATA => 'data_provider_import_name',
                ]),
                'expectedStep' => new PendingImportResolutionStep([], [], 'import_name', 'data_provider_import_name'),
            ],
            'import name and inline data' => [
                'stepData' => new StepData([
                    StepData::KEY_USE => 'import_name',
                    StepData::KEY_DATA => [
                        'data_set_1' => [
                            'expected_title' => 'Foo',
                        ],
                    ]
                ]),
                'expectedStep' => (new PendingImportResolutionStep(
                    [],
                    [],
                    'import_name',
                    ''
                ))->withDataSets([
                    'data_set_1' => new DataSet([
                        'expected_title' => 'Foo',
                    ]),
                ]),
            ],
            'import name, data provider name, actions and assertions' => [
                'stepData' => new StepData([
                    StepData::KEY_USE => 'import_name',
                    StepData::KEY_DATA => 'data_provider_import_name',
                    StepData::KEY_ACTIONS => [
                        'click ".selector"',
                    ],
                    StepData::KEY_ASSERTIONS => [
                        '".selector" exists',
                    ],
                ]),
                'expectedStep' => new PendingImportResolutionStep(
                    [
                        new InteractionAction(
                            ActionTypes::CLICK,
                            new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                new Value(
                                    ValueTypes::STRING,
                                    '.selector'
                                )
                            ),
                            '".selector"'
                        ),
                    ],
                    [
                        new Assertion(
                            '".selector" exists',
                            new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                new Value(
                                    ValueTypes::STRING,
                                    '.selector'
                                )
                            ),
                            AssertionComparisons::EXISTS
                        ),
                    ],
                    'import_name',
                    'data_provider_import_name'
                ),
            ],
        ];
    }
}
