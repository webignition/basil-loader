<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Functional\Builder;

use Symfony\Component\Yaml\Parser as YamlParser;
use webignition\BasilParser\Builder\StepBuilder;
use webignition\BasilParser\Factory\StepFactory;
use webignition\BasilParser\Loader\StepLoader;
use webignition\BasilParser\Loader\YamlLoader;
use webignition\BasilParser\Model\Action\ActionTypes;
use webignition\BasilParser\Model\Action\InteractionAction;
use webignition\BasilParser\Model\Assertion\Assertion;
use webignition\BasilParser\Model\Assertion\AssertionComparisons;
use webignition\BasilParser\Model\Identifier\Identifier;
use webignition\BasilParser\Model\Identifier\IdentifierTypes;
use webignition\BasilParser\Model\Step\Step;
use webignition\BasilParser\Model\Step\StepInterface;
use webignition\BasilParser\Model\Value\Value;
use webignition\BasilParser\Model\Value\ValueTypes;

class StepBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var StepBuilder
     */
    private $stepBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $stepFactory = new StepFactory();
        $yamlParser = new YamlParser();
        $yamlLoader = new YamlLoader($yamlParser);
        $stepLoader = new StepLoader($yamlLoader, $stepFactory);

        $this->stepBuilder = new StepBuilder($stepFactory, $stepLoader);
    }

    /**
     * @dataProvider buildSuccessDataProvider
     */
    public function testBuildSuccess(
        array $stepData,
        array $stepImportPaths,
        array $dataProviderImportPaths,
        StepInterface $expectedStep
    ) {
        $step = $this->stepBuilder->build('Step Name', $stepData, $stepImportPaths, $dataProviderImportPaths);

        $this->assertInstanceOf(StepInterface::class, $step);
        $this->assertEquals($expectedStep, $step);
    }

    public function buildSuccessDataProvider(): array
    {
        return [
            'no imports, no actions, no assertions' => [
                'stepData' => [],
                'stepImportPaths' => [],
                'dataProviderImportPaths' => [],
                'expectedStep' => new Step([], []),
            ],
            'no imports, empty actions, empty assertions' => [
                'stepData' => [
                    StepFactory::KEY_ACTIONS => [],
                    StepFactory::KEY_ASSERTIONS => [],
                ],
                'stepImportPaths' => [],
                'dataProviderImportPaths' => [],
                'expectedStep' => new Step([], []),
            ],
            'no imports, has actions, has assertions' => [
                'stepData' => [
                    StepFactory::KEY_ACTIONS => [
                        'click ".selector"',
                    ],
                    StepFactory::KEY_ASSERTIONS => [
                        '$page.title is "Example"',
                    ],
                ],
                'stepImportPaths' => [],
                'dataProviderImportPaths' => [],
                'expectedStep' => new Step(
                    [
                        new InteractionAction(
                            ActionTypes::CLICK,
                            new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                '.selector'
                            ),
                            '".selector"'
                        )
                    ],
                    [
                        new Assertion(
                            '$page.title is "Example"',
                            new Identifier(
                                IdentifierTypes::PAGE_OBJECT_PARAMETER,
                                '$page.title'
                            ),
                            AssertionComparisons::IS,
                            new Value(
                                ValueTypes::STRING,
                                'Example'
                            )
                        )
                    ]
                ),
            ],
            'import step' => [
                'stepData' => [

                ],
                'stepImportPaths' => [],
                'dataProviderImportPaths' => [],
                'expectedStep' => new Step([], []),
            ],
        ];
    }
}
