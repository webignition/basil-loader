<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Factory;

use Nyholm\Psr7\Uri;
use webignition\BasilModel\Action\ActionTypes;
use webignition\BasilModel\Action\InputAction;
use webignition\BasilModel\Action\InteractionAction;
use webignition\BasilModel\Assertion\Assertion;
use webignition\BasilModel\Assertion\AssertionComparisons;
use webignition\BasilModel\Identifier\Identifier;
use webignition\BasilModel\Identifier\IdentifierTypes;
use webignition\BasilModel\Page\Page;
use webignition\BasilModel\Step\Step;
use webignition\BasilModel\Step\StepInterface;
use webignition\BasilModel\Value\Value;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilParser\DataStructure\Step as StepData;
use webignition\BasilParser\Factory\StepFactory;
use webignition\BasilParser\Provider\Page\EmptyPageProvider;
use webignition\BasilParser\Provider\Page\PageProviderInterface;
use webignition\BasilParser\Provider\Page\PopulatedPageProvider;
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
    public function testCreateFromStepData(
        StepData $stepData,
        PageProviderInterface $pageProvider,
        StepInterface $expectedStep
    ) {
        $step = $this->stepFactory->createFromStepData($stepData, $pageProvider);

        $this->assertEquals($expectedStep, $step);
    }

    public function createFromStepDataDataProvider(): array
    {
        return [
            'empty step data' => [
                'stepData' => new StepData([]),
                'pages' => new EmptyPageProvider(),
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
                'pages' => new EmptyPageProvider(),
                'expectedStep' => new Step([], []),
            ],
            'actions only' => [
                'stepData' => new StepData([
                    StepData::KEY_ACTIONS => [
                        'click ".selector"',
                        'set ".input" to "value"',
                    ],
                ]),
                'pages' => new EmptyPageProvider(),
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
                'pages' => new EmptyPageProvider(),
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
                'pages' => new PopulatedPageProvider([
                    'page_import_name' => new Page(
                        new Uri('http://example.com'),
                        [
                            'element_name' => new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                new Value(
                                    ValueTypes::STRING,
                                    '.selector'
                                )
                            ),
                        ]
                    )
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
                            'page_import_name.elements.element_name'
                        )
                    ],
                    [
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
                    ]
                ),
            ],
        ];
    }
}
