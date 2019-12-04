<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Tests\Unit;

use webignition\BasilLoader\StepLoader;
use webignition\BasilLoader\Tests\Services\FixturePathFinder;
use webignition\BasilModels\Action\InteractionAction;
use webignition\BasilModels\Assertion\ComparisonAssertion;
use webignition\BasilModels\Step\Step;
use webignition\BasilModels\Step\StepInterface;

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
                            'click $".button"',
                            'click',
                            '$".button"',
                            '$".button"'
                        ),
                    ],
                    [
                        new ComparisonAssertion(
                            '$".heading" includes "example"',
                            '$".heading"',
                            'includes',
                            '"example"'
                        ),
                    ]
                ),
            ],
            'deferred import' => [
                'path' => FixturePathFinder::find('Step/deferred_import.yml'),
                'expectedStep' => (new Step([], []))
                    ->withImportName('no_parameters_import_name'),
            ],
        ];
    }
}
