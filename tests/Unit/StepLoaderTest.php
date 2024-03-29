<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Tests\Unit;

use webignition\BasilLoader\StepLoader;
use webignition\BasilLoader\Tests\Services\FixturePathFinder;
use webignition\BasilModels\Model\Step\Step;
use webignition\BasilModels\Model\Step\StepInterface;
use webignition\BasilModels\Parser\ActionParser;
use webignition\BasilModels\Parser\AssertionParser;

class StepLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider loadDataProvider
     */
    public function testLoad(string $path, StepInterface $expectedStep): void
    {
        $stepLoader = StepLoader::createLoader();

        $step = $stepLoader->load($path);

        $this->assertEquals($expectedStep, $step);
    }

    /**
     * @return array<mixed>
     */
    public function loadDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $assertionParser = AssertionParser::create();

        return [
            'empty' => [
                'path' => FixturePathFinder::find('Empty/empty.yml'),
                'expectedStep' => new Step([], []),
            ],
            'literal' => [
                'path' => FixturePathFinder::find('Step/no-parameters.yml'),
                'expectedStep' => new Step(
                    [
                        $actionParser->parse('click $".button"'),
                    ],
                    [
                        $assertionParser->parse('$".heading" includes "example"'),
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
