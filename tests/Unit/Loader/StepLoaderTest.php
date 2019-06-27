<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Loader;

use webignition\BasilModel\Action\ActionTypes;
use webignition\BasilModel\Action\InteractionAction;
use webignition\BasilModel\Assertion\Assertion;
use webignition\BasilModel\Assertion\AssertionComparisons;
use webignition\BasilModel\Identifier\Identifier;
use webignition\BasilModel\Identifier\IdentifierTypes;
use webignition\BasilModel\Step\Step;
use webignition\BasilModel\Step\StepInterface;
use webignition\BasilModel\Value\Value;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilParser\Tests\Services\FixturePathFinder;
use webignition\BasilParser\Tests\Services\StepLoaderFactory;

class StepLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider loadDataProvider
     */
    public function testLoad(string $path, StepInterface $expectedStep)
    {
        $stepLoader = StepLoaderFactory::create();

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
            'non-empty' => [
                'path' => FixturePathFinder::find('Step/no-parameters.yml'),
                'expectedStep' => new Step(
                    [
                        new InteractionAction(
                            ActionTypes::CLICK,
                            new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                '.button'
                            ),
                            '".button"'
                        ),
                    ],
                    [
                        new Assertion(
                            '".heading" includes "Hello World"',
                            new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                '.heading'
                            ),
                            AssertionComparisons::INCLUDES,
                            new Value(
                                ValueTypes::STRING,
                                'Hello World'
                            )
                        ),
                    ]
                ),
            ],
        ];
    }
}
