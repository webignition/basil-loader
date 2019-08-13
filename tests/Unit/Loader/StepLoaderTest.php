<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Loader;

use webignition\BasilModel\Action\ActionTypes;
use webignition\BasilModel\Action\InteractionAction;
use webignition\BasilModel\Action\WaitAction;
use webignition\BasilModel\Assertion\Assertion;
use webignition\BasilModel\Assertion\AssertionComparisons;
use webignition\BasilModel\Step\Step;
use webignition\BasilModel\Step\StepInterface;
use webignition\BasilModel\Value\ElementValue;
use webignition\BasilModel\Value\LiteralValue;
use webignition\BasilParser\Loader\StepLoader;
use webignition\BasilParser\Provider\DataSet\DataSetProviderInterface;
use webignition\BasilParser\Provider\DataSet\EmptyDataSetProvider;
use webignition\BasilParser\Provider\Page\EmptyPageProvider;
use webignition\BasilParser\Provider\Page\PageProviderInterface;
use webignition\BasilParser\Provider\Step\DeferredStepProvider;
use webignition\BasilParser\Provider\Step\EmptyStepProvider;
use webignition\BasilParser\Provider\Step\PopulatedStepProvider;
use webignition\BasilParser\Provider\Step\StepProviderInterface;
use webignition\BasilParser\Tests\Services\FixturePathFinder;
use webignition\BasilParser\Tests\Services\TestIdentifierFactory;

class StepLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider loadDataProvider
     */
    public function testLoad(
        string $path,
        StepProviderInterface $stepProvider,
        DataSetProviderInterface $dataSetProvider,
        PageProviderInterface $pageProvider,
        StepInterface $expectedStep
    ) {
        $stepLoader = StepLoader::createLoader();

        $step = $stepLoader->load($path, $stepProvider, $dataSetProvider, $pageProvider);

        $this->assertEquals($expectedStep, $step);
    }

    public function loadDataProvider(): array
    {
        return [
            'empty' => [
                'path' => FixturePathFinder::find('Empty/empty.yml'),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => new Step([], []),
            ],
            'literal' => [
                'path' => FixturePathFinder::find('Step/no-parameters.yml'),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => new Step(
                    [
                        new InteractionAction(
                            'click ".button"',
                            ActionTypes::CLICK,
                            TestIdentifierFactory::createCssElementIdentifier('.button'),
                            '".button"'
                        ),
                    ],
                    [
                        new Assertion(
                            '".heading" includes "example"',
                            new ElementValue(
                                TestIdentifierFactory::createCssElementIdentifier('.heading')
                            ),
                            AssertionComparisons::INCLUDES,
                            LiteralValue::createStringValue('example')
                        ),
                    ]
                ),
            ],
            'deferred import with populated step provider' => [
                'path' => FixturePathFinder::find('Step/deferred_import.yml'),
                'stepProvider' => new PopulatedStepProvider([
                    'no_parameters_import_name' => new Step(
                        [
                            new WaitAction('wait 20', LiteralValue::createStringValue('20')),
                        ],
                        [
                            new Assertion(
                                '".selector" exists',
                                new ElementValue(
                                    TestIdentifierFactory::createCssElementIdentifier('.selector')
                                ),
                                AssertionComparisons::EXISTS
                            )
                        ]
                    )
                ]),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => new Step(
                    [
                        new WaitAction('wait 20', LiteralValue::createStringValue('20')),
                    ],
                    [
                        new Assertion(
                            '".selector" exists',
                            new ElementValue(
                                TestIdentifierFactory::createCssElementIdentifier('.selector')
                            ),
                            AssertionComparisons::EXISTS
                        )
                    ]
                ),
            ],
            'deferred import with deferred step provider' => [
                'path' => FixturePathFinder::find('Step/deferred_import.yml'),
                'stepProvider' => new DeferredStepProvider(
                    StepLoader::createLoader(),
                    [
                        'no_parameters_import_name' => FixturePathFinder::find('Step/no-parameters.yml'),
                    ]
                ),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'pageProvider' => new EmptyPageProvider(),
                'expectedStep' => new Step(
                    [
                        new InteractionAction(
                            'click ".button"',
                            ActionTypes::CLICK,
                            TestIdentifierFactory::createCssElementIdentifier('.button'),
                            '".button"'
                        ),
                    ],
                    [
                        new Assertion(
                            '".heading" includes "example"',
                            new ElementValue(
                                TestIdentifierFactory::createCssElementIdentifier('.heading')
                            ),
                            AssertionComparisons::INCLUDES,
                            LiteralValue::createStringValue('example')
                        ),
                    ]
                ),
            ],
        ];
    }
}
