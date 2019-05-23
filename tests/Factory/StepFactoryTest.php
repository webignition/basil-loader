<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Factory;

use webignition\BasilParser\Factory\StepFactory;
use webignition\BasilParser\Model\Action\ActionTypes;
use webignition\BasilParser\Model\Action\InputAction;
use webignition\BasilParser\Model\Action\InteractionAction;
use webignition\BasilParser\Model\Assertion\Assertion;
use webignition\BasilParser\Model\Assertion\AssertionComparisons;
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
    public function testCreateFromStepData(array $stepData, StepInterface $expectedStep)
    {
        $step = $this->stepFactory->createFromStepData($stepData);

        $this->assertEquals($expectedStep, $step);
    }

    public function createFromStepDataDataProvider(): array
    {
        return [
            'empty step data' => [
                'stepData' => [],
                'expectedStep' => new Step([], []),
            ],
            'empty actions and empty assertions' => [
                'stepData' => [
                    StepFactory::KEY_ACTIONS => [
                        '',
                        ' ',
                    ],
                    StepFactory::KEY_ASSERTIONS => [
                        '',
                        ' ',
                    ],
                ],
                'expectedStep' => new Step([], []),
            ],
            'actions only' => [
                'stepData' => [
                    StepFactory::KEY_ACTIONS => [
                        'click ".selector"',
                        'set ".input" to "value"',
                    ],
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
                    ]
                ),
            ],
        ];
    }
}
