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
use webignition\BasilParser\Model\Step\StepInterface;

class StepTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(array $actions, array $assertions, array $expectedActions, array $expectedAssertions)
    {
        $step = new Step($actions, $assertions);

        $this->assertEquals($expectedActions, $step->getActions());
        $this->assertEquals($expectedAssertions, $step->getAssertions());
    }

    public function createDataProvider(): array
    {
        return [
            'no actions, no assertions' => [
                'actions' => [],
                'assertions' => [],
                'expectedActions' => [],
                'expectedAssertions' => [],
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
                'expectedActions' => [],
                'expectedAssertions' => [],
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
            ],
        ];
    }

    /**
     * @dataProvider withDataSetsDataProvider
     */
    public function testWithDataSets(
        StepInterface $step,
        array $dataSets,
        array $expectedDataSets
    ) {
        $currentDataSets = $step->getDataSets();

        $mutatedStep = $step->withDataSets($dataSets);

        $this->assertNotSame($mutatedStep, $step);
        $this->assertEquals($expectedDataSets, $mutatedStep->getDataSets());
        $this->assertSame($currentDataSets, $step->getDataSets());
    }

    public function withDataSetsDataProvider(): array
    {
        return [
            'no existing data sets, empty data sets' => [
                'step' => new Step([], []),
                'dataSets' => [],
                'expectedDataSets' => [],
            ],
            'no existing data sets, non-empty data sets' => [
                'step' => new Step([], []),
                'dataSets' => [
                    'one' => 1,
                    'two' => 'two',
                    'three' => new DataSet([]),
                ],
                'expectedDataSets' => [
                    'three' => new DataSet([]),
                ],
            ],
            'has existing data sets, empty data sets' => [
                'step' => (new Step([], []))->withDataSets([
                    'one' => new DataSet([]),
                ]),
                'dataSets' => [],
                'expectedDataSets' => [],
            ],
            'has existing data sets, non-empty data sets' => [
                'step' => (new Step([], []))->withDataSets([
                    'one' => new DataSet([]),
                ]),
                'dataSets' => [
                    'two' => new DataSet([]),
                ],
                'expectedDataSets' => [
                    'two' => new DataSet([]),
                ],
            ],
        ];
    }

    /**
     * @dataProvider withElementReferencesDataProvider
     */
    public function testWithElementReferences(
        StepInterface $step,
        array $elementReferences,
        array $expectedElementReferences
    ) {
        $currentElementReferences = $step->getElementReferences();

        $mutatedStep = $step->withElementReferences($elementReferences);

        $this->assertNotSame($mutatedStep, $step);
        $this->assertEquals($expectedElementReferences, $mutatedStep->getElementReferences());
        $this->assertSame($currentElementReferences, $step->getElementReferences());
    }

    public function withElementReferencesDataProvider(): array
    {
        return [
            'no existing element references, empty element references' => [
                'step' => new Step([], []),
                'elementReferences' => [],
                'expectedElementReferences' => [],
            ],
            'no existing element references, non-empty element references' => [
                'step' => new Step([], []),
                'elementReferences' => [
                    'input' => 'page_model.elements.input',
                ],
                'expectedElementReferences' => [
                    'input' => 'page_model.elements.input',
                ],
            ],
            'has existing element references, empty element references' => [
                'step' => (new Step([], []))->withElementReferences([
                    'input' => 'page_model.elements.input',
                ]),
                'elementReferences' => [],
                'expectedElementReferences' => [],
            ],
            'has existing element references, non-empty element references' => [
                'step' => (new Step([], []))->withElementReferences([
                    'input' => 'page_model.elements.input',
                ]),
                'elementReferences' => [
                    'button' => 'page_model.elements.button',
                ],
                'expectedElementReferences' => [
                    'button' => 'page_model.elements.button',
                ],
            ],
        ];
    }
}
