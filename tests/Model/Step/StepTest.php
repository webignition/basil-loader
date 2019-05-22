<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Model\Step;

use webignition\BasilParser\Model\Action\WaitAction;
use webignition\BasilParser\Model\Assertion\Assertion;
use webignition\BasilParser\Model\Assertion\AssertionComparisons;
use webignition\BasilParser\Model\DataSet\DataSet;
use webignition\BasilParser\Model\Identifier\Identifier;
use webignition\BasilParser\Model\Identifier\IdentifierTypes;
use webignition\BasilParser\Model\Step\Step;

class StepTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(
        array $actions,
        array $assertions,
        array $dataSets,
        array $elementReferences,
        array $expectedActions,
        array $expectedAssertions,
        array $expectedDataSets,
        array $expectedElementReferences
    ) {
        $step = new Step($actions, $assertions, $dataSets, $elementReferences);

        $this->assertEquals($expectedActions, $step->getActions());
        $this->assertEquals($expectedAssertions, $step->getAssertions());
        $this->assertEquals($expectedDataSets, $step->getDataSets());
        $this->assertEquals($expectedElementReferences, $step->getElementReferences());
    }

    public function createDataProvider(): array
    {
        return [
            'no actions, no assertions' => [
                'actions' => [],
                'assertions' => [],
                'dataSets' => [],
                'elementReferences' => [],
                'expectedActions' => [],
                'expectedAssertions' => [],
                'expectedDataSets' => [],
                'expectedElementReferences' => [],
            ],
            'all non-actions, all non-assertions' => [
                'actions' => [
                    'foo',
                    'bar',
                ],
                'assertions' => [
                    1,
                    2,
                ],
                'dataSets' => [],
                'elementReferences' => [],
                'expectedActions' => [],
                'expectedAssertions' => [],
                'expectedDataSets' => [],
                'expectedElementReferences' => [],
            ],
            'has actions, has assertions, some not correct types' => [
                'actions' => [
                    'foo',
                    new WaitAction('5'),
                    'bar',
                ],
                'assertions' => [
                    1,
                    2,
                    new Assertion(
                        '".selector" is "foo"',
                        new Identifier(
                            IdentifierTypes::CSS_SELECTOR,
                            '.selector'
                        ),
                        AssertionComparisons::IS
                    ),
                ],
                'dataSets' => [],
                'elementReferences' => [],
                'expectedActions' => [
                    new WaitAction('5'),
                ],
                'expectedAssertions' => [
                    new Assertion(
                        '".selector" is "foo"',
                        new Identifier(
                            IdentifierTypes::CSS_SELECTOR,
                            '.selector'
                        ),
                        AssertionComparisons::IS
                    ),
                ],
                'expectedDataSets' => [],
                'expectedElementReferences' => [],
            ],
            'has data sets' => [
                'actions' => [],
                'assertions' => [],
                'dataSets' => [
                    'one' => 1,
                    'two' => 'two',
                    'three' => new DataSet([]),
                ],
                'elementReferences' => [],
                'expectedActions' => [],
                'expectedAssertions' => [],
                'expectedDataSets' => [
                    'three' => new DataSet([]),
                ],
                'expectedElementReferences' => [],
            ],
            'has element references' => [
                'actions' => [],
                'assertions' => [],
                'dataSets' => [],
                'elementReferences' => [
                    'input' => 'page_model.elements.input',
                ],
                'expectedActions' => [],
                'expectedAssertions' => [],
                'expectedDataSets' => [],
                'expectedElementReferences' => [
                    'input' => 'page_model.elements.input',
                ],
            ],
        ];
    }
}
