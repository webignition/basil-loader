<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Loader;

use webignition\BasilParser\Factory\StepFactory;
use webignition\BasilParser\Loader\StepLoader;
use webignition\BasilParser\Loader\YamlLoader;
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
use webignition\BasilParser\Tests\Services\StepFactoryFactory;

class StepLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider loadDataProvider
     */
    public function testLoad(array $yamlLoaderReturnValue, StepInterface $expectedStep)
    {
        $path = 'step.yml';

        $yamlLoader = \Mockery::mock(YamlLoader::class);
        $yamlLoader
            ->shouldReceive('loadArray')
            ->with($path)
            ->andReturn($yamlLoaderReturnValue);

        $stepFactory = StepFactoryFactory::create();

        $stepLoader = new StepLoader($yamlLoader, $stepFactory);

        $page = $stepLoader->load($path);

        $this->assertEquals($expectedStep, $page);
    }

    public function loadDataProvider(): array
    {
        return [
            'empty' => [
                'yamlLoaderReturnValue' => [],
                'expectedStep' => new Step([], []),
            ],
            'non-empty' => [
                'yamlLoaderReturnValue' => [
                    StepFactory::KEY_ACTIONS => [
                        'click ".selector"',
                        'set $elements.search_input to $data.query_term',
                    ],
                    StepFactory::KEY_ASSERTIONS => [
                        '$page.title is $data.expected_title',
                        '".new-selector" exists',
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
                                IdentifierTypes::ELEMENT_PARAMETER,
                                '$elements.search_input'
                            ),
                            new Value(
                                ValueTypes::DATA_PARAMETER,
                                '$data.query_term'
                            ),
                            '$elements.search_input to $data.query_term'
                        ),
                    ],
                    [
                        new Assertion(
                            '$page.title is $data.expected_title',
                            new Identifier(
                                IdentifierTypes::PAGE_OBJECT_PARAMETER,
                                '$page.title'
                            ),
                            AssertionComparisons::IS,
                            new Value(
                                ValueTypes::DATA_PARAMETER,
                                '$data.expected_title'
                            )
                        ),
                        new Assertion(
                            '".new-selector" exists',
                            new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                '.new-selector'
                            ),
                            AssertionComparisons::EXISTS
                        ),
                    ]
                ),
            ],
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        \Mockery::close();
    }
}
