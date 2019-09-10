<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilLoader\Tests\Unit;

use webignition\BasilLoader\StepLoader;
use webignition\BasilLoader\Tests\Services\FixturePathFinder;
use webignition\BasilModel\Action\ActionTypes;
use webignition\BasilModel\Action\InteractionAction;
use webignition\BasilModel\Assertion\Assertion;
use webignition\BasilModel\Assertion\AssertionComparisons;
use webignition\BasilModel\Step\PendingImportResolutionStep;
use webignition\BasilModel\Step\Step;
use webignition\BasilModel\Step\StepInterface;
use webignition\BasilModel\Value\CssSelector;
use webignition\BasilModel\Value\ElementValue;
use webignition\BasilModel\Value\LiteralValue;
use webignition\BasilTestIdentifierFactory\TestIdentifierFactory;

class StepLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider loadDataProvider
     */
    public function testLoad(string $path, StepInterface $expectedStep)
    {
        $stepLoader = StepLoader::createLoader();

        $step = $stepLoader->load($path);

        $this->assertEquals($expectedStep, $step);
    }

    public function loadDataProvider(): array
    {
        return [
            'empty' => [
                'path' => FixturePathFinder::find('Empty/empty.yml'),
                'expectedStep' => new Step([], []),
            ],
            'literal' => [
                'path' => FixturePathFinder::find('Step/no-parameters.yml'),
                'expectedStep' => new Step(
                    [
                        new InteractionAction(
                            'click ".button"',
                            ActionTypes::CLICK,
                            TestIdentifierFactory::createElementIdentifier(new CssSelector('.button')),
                            '".button"'
                        ),
                    ],
                    [
                        new Assertion(
                            '".heading" includes "example"',
                            new ElementValue(
                                TestIdentifierFactory::createElementIdentifier(new CssSelector('.heading'))
                            ),
                            AssertionComparisons::INCLUDES,
                            new LiteralValue('example')
                        ),
                    ]
                ),
            ],
            'deferred import' => [
                'path' => FixturePathFinder::find('Step/deferred_import.yml'),
                'expectedStep' => new PendingImportResolutionStep(
                    new Step([], []),
                    'no_parameters_import_name',
                    ''
                ),
            ],
        ];
    }
}
