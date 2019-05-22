<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Factory;

use webignition\BasilParser\Factory\StepFactory;
use webignition\BasilParser\Model\Action\ActionTypes;
use webignition\BasilParser\Model\Action\InputAction;
use webignition\BasilParser\Model\Action\InteractionAction;
use webignition\BasilParser\Model\Assertion\Assertion;
use webignition\BasilParser\Model\Assertion\AssertionComparisons;
use webignition\BasilParser\Model\DataSet\DataSet;
use webignition\BasilParser\Model\Identifier\Identifier;
use webignition\BasilParser\Model\Identifier\IdentifierTypes;
use webignition\BasilParser\Model\Step\Step;
use webignition\BasilParser\Model\Step\StepInterface;
use webignition\BasilParser\Model\Value\Value;
use webignition\BasilParser\Model\Value\ValueTypes;

class StepFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var StepFactory
     */
    private $stepFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stepFactory = new StepFactory();
    }

    /**
     * @dataProvider createFromStepDataDataProvider
     */
    public function testCreateFromStepData(
        array $stepData,
        array $dataSets,
        array $elementReferences,
        StepInterface $expectedStep
    ) {
        $step = $this->stepFactory->createFromStepData($stepData, $dataSets, $elementReferences);

        $this->assertEquals($expectedStep, $step);
    }

    public function createFromStepDataDataProvider(): array
    {
        return [
            'empty step data' => [
                'stepData' => [],
                'dataSets' => [],
                'elementReferences' => [],
                'expectedStep' => new Step([], [], [], []),
            ],
            'actions only' => [
                'stepData' => [
                    StepFactory::KEY_ACTIONS => [
                        'click ".selector"',
                        'set ".input" to "value"',
                    ],
                ],
                'dataSets' => [],
                'elementReferences' => [],
                'expectedStep' => new Step(
                    [
                        new InteractionAction(
                            ActionTypes::CLICK,
                            new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                '.selector'
                            ),
                            '".selector"'
                        ),
                        new InputAction(
                            new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                '.input'
                            ),
                            new Value(
                                ValueTypes::STRING,
                                'value'
                            ),
                            '".input" to "value"'
                        )
                    ],
                    [],
                    [],
                    []
                ),
            ],
            'assertions only' => [
                'stepData' => [
                    StepFactory::KEY_ASSERTIONS => [
                        '".selector" is "value"',
                        '".input" exists'
                    ],
                ],
                'dataSets' => [],
                'elementReferences' => [],
                'expectedStep' => new Step(
                    [
                    ],
                    [
                        new Assertion(
                            '".selector" is "value"',
                            new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                '.selector'
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
                                '.input'
                            ),
                            AssertionComparisons::EXISTS
                        ),
                    ],
                    [],
                    []
                ),
            ],
            'actions, assertions, data sets and element references' => [
                'stepData' => [
                    StepFactory::KEY_ACTIONS => [
                        'click ".selector"',
                    ],
                    StepFactory::KEY_ASSERTIONS => [
                        '".selector" is "value"',
                    ],
                ],
                'dataSets' => [
                    new DataSet([]),
                ],
                'elementReferences' => [
                    'foo' => 'page_model.elements.element_name'
                ],
                'expectedStep' => new Step(
                    [
                        new InteractionAction(
                            ActionTypes::CLICK,
                            new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                '.selector'
                            ),
                            '".selector"'
                        ),
                    ],
                    [
                        new Assertion(
                            '".selector" is "value"',
                            new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                '.selector'
                            ),
                            AssertionComparisons::IS,
                            new Value(
                                ValueTypes::STRING,
                                'value'
                            )
                        ),
                    ],
                    [
                        new DataSet([]),
                    ],
                    [
                        'foo' => 'page_model.elements.element_name'
                    ]
                ),
            ],
        ];
    }
}
