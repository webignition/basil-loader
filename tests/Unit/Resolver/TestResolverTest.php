<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Tests\Unit\Resolver;

use webignition\BasilContextAwareException\ContextAwareExceptionInterface;
use webignition\BasilContextAwareException\ExceptionContext\ExceptionContextInterface;
use webignition\BasilLoader\Resolver\TestResolver;
use webignition\BasilLoader\Resolver\UnknownElementException;
use webignition\BasilLoader\Resolver\UnknownPageElementException;
use webignition\BasilModels\Model\Action\Action;
use webignition\BasilModels\Model\Action\ResolvedAction;
use webignition\BasilModels\Model\Assertion\Assertion;
use webignition\BasilModels\Model\Assertion\ResolvedAssertion;
use webignition\BasilModels\Model\DataSet\DataSetCollection;
use webignition\BasilModels\Model\Page\Page;
use webignition\BasilModels\Model\Step\Step;
use webignition\BasilModels\Model\Step\StepCollection;
use webignition\BasilModels\Model\Test\Configuration;
use webignition\BasilModels\Model\Test\Test;
use webignition\BasilModels\Model\Test\TestInterface;
use webignition\BasilModels\Provider\DataSet\DataSetProvider;
use webignition\BasilModels\Provider\DataSet\DataSetProviderInterface;
use webignition\BasilModels\Provider\DataSet\EmptyDataSetProvider;
use webignition\BasilModels\Provider\Exception\UnknownItemException;
use webignition\BasilModels\Provider\Page\EmptyPageProvider;
use webignition\BasilModels\Provider\Page\PageProvider;
use webignition\BasilModels\Provider\Page\PageProviderInterface;
use webignition\BasilModels\Provider\Step\EmptyStepProvider;
use webignition\BasilModels\Provider\Step\StepProvider;
use webignition\BasilModels\Provider\Step\StepProviderInterface;
use webignition\BasilParser\ActionParser;
use webignition\BasilParser\AssertionParser;
use webignition\BasilParser\StepParser;
use webignition\BasilParser\Test\TestParser;

class TestResolverTest extends \PHPUnit\Framework\TestCase
{
    private TestResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = TestResolver::createResolver();
    }

    /**
     * @dataProvider resolveSuccessDataProvider
     */
    public function testResolveSuccess(
        TestInterface $test,
        PageProviderInterface $pageProvider,
        StepProviderInterface $stepProvider,
        DataSetProviderInterface $dataSetProvider,
        TestInterface $expectedTest
    ): void {
        $resolvedTest = $this->resolver->resolve($test, $pageProvider, $stepProvider, $dataSetProvider);

        $this->assertEquals($expectedTest, $resolvedTest);
    }

    /**
     * @return array<mixed>
     */
    public function resolveSuccessDataProvider(): array
    {
        $actionParser = ActionParser::create();
        $assertionParser = AssertionParser::create();

        $expectedResolvedDataTest = new Test(
            new Configuration('', ''),
            new StepCollection([
                'step name' => (new Step(
                    [
                        new Action(
                            'set $".action-selector" to $data.key1',
                            'set',
                            '$".action-selector" to $data.key1',
                            '$".action-selector"',
                            '$data.key1'
                        )
                    ],
                    [
                        new Assertion(
                            '$".assertion-selector" is $data.key2',
                            '$".assertion-selector"',
                            'is',
                            '$data.key2'
                        )
                    ]
                ))->withData(new DataSetCollection([
                    '0' => [
                        'key1' => 'key1value1',
                        'key2' => 'key2value1',
                    ],
                    '1' => [
                        'key1' => 'key1value2',
                        'key2' => 'key2value2',
                    ],
                ])),
            ])
        );

        $testParser = TestParser::create();
        $stepParser = StepParser::create();

        return [
            'empty test' => [
                'test' => $testParser->parse([]),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedTest' => new Test(new Configuration('', ''), new StepCollection([])),
            ],
            'empty test with path' => [
                'test' => $testParser->parse([])->withPath('test.yml'),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedTest' => (
                    new Test(
                        new Configuration('', ''),
                        new StepCollection([])
                    )
                )->withPath('test.yml'),
            ],
            'configuration is resolved' => [
                'test' => $testParser->parse([
                    'config' => [
                        'url' => '$page_import_name.url',
                    ],
                ]),
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page('page_import_name', 'http://example.com/'),
                ]),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedTest' => new Test(
                    new Configuration('', 'http://example.com/'),
                    new StepCollection([])
                ),
            ],
            'empty step' => [
                'test' => $testParser->parse([
                    'step name' => [],
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedTest' => new Test(new Configuration('', ''), new StepCollection([
                    'step name' => new Step([], []),
                ])),
            ],
            'no imports, actions and assertions require no resolution' => [
                'test' => $testParser->parse([
                    'step name' => [
                        'actions' => [
                            'click $".action-selector"',
                        ],
                        'assertions' => [
                            '$".assertion-selector" exists',
                        ],
                    ],
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedTest' => new Test(
                    new Configuration('', ''),
                    new StepCollection([
                        'step name' => new Step(
                            [
                                new Action(
                                    'click $".action-selector"',
                                    'click',
                                    '$".action-selector"',
                                    '$".action-selector"'
                                )
                            ],
                            [
                                new Assertion(
                                    '$".assertion-selector" exists',
                                    '$".assertion-selector"',
                                    'exists'
                                )
                            ]
                        ),
                    ])
                ),
            ],
            'actions and assertions require resolution of page imports' => [
                'test' => $testParser->parse([
                    'step name' => [
                        'actions' => [
                            'click $page_import_name.elements.action_selector',
                        ],
                        'assertions' => [
                            '$page_import_name.elements.assertion_selector exists',
                        ],
                    ],
                ]),
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page(
                        'page_import_name',
                        'http://example.com',
                        [
                            'action_selector' => '$".action-selector"',
                            'assertion_selector' => '$".assertion-selector"',
                        ]
                    ),
                ]),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedTest' => new Test(
                    new Configuration('', ''),
                    new StepCollection([
                        'step name' => new Step(
                            [
                                new ResolvedAction(
                                    $actionParser->parse('click $page_import_name.elements.action_selector'),
                                    '$".action-selector"'
                                ),
                            ],
                            [
                                new ResolvedAssertion(
                                    $assertionParser->parse('$page_import_name.elements.assertion_selector exists'),
                                    '$".assertion-selector"'
                                ),
                            ]
                        ),
                    ])
                ),
            ],
            'empty step imports step, imported actions and assertions require no resolution' => [
                'test' => $testParser->parse([
                    'step name' => [
                        'use' => 'step_import_name',
                    ],
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new StepProvider([
                    'step_import_name' => $stepParser->parse([
                        'actions' => [
                            'click $".action-selector"',
                        ],
                        'assertions' => [
                            '$".assertion-selector" exists',
                        ],
                    ]),
                ]),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedTest' => new Test(
                    new Configuration('', ''),
                    new StepCollection([
                        'step name' => new Step(
                            [
                                new Action(
                                    'click $".action-selector"',
                                    'click',
                                    '$".action-selector"',
                                    '$".action-selector"'
                                )
                            ],
                            [
                                new Assertion(
                                    '$".assertion-selector" exists',
                                    '$".assertion-selector"',
                                    'exists'
                                )
                            ]
                        ),
                    ])
                ),
            ],
            'empty step imports step, imported actions and assertions require element resolution' => [
                'test' => $testParser->parse([
                    'step name' => [
                        'use' => 'step_import_name',
                        'elements' => [
                            'elements_action_selector' => '$page_import_name.elements.page_action_selector',
                            'elements_assertion_selector' => '$page_import_name.elements.page_assertion_selector',
                        ],
                    ],
                ]),
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page(
                        'page_import_name',
                        'http://example.com',
                        [
                            'page_action_selector' => '$".action-selector"',
                            'page_assertion_selector' => '$".assertion-selector"',
                        ]
                    ),
                ]),
                'stepProvider' => new StepProvider([
                    'step_import_name' => $stepParser->parse([
                        'actions' => [
                            'click $elements.elements_action_selector'
                        ],
                        'assertions' => [
                            '$elements.elements_assertion_selector exists'
                        ],
                    ]),
                ]),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedTest' => new Test(
                    new Configuration('', ''),
                    new StepCollection([
                        'step name' => new Step(
                            [
                                new ResolvedAction(
                                    $actionParser->parse('click $elements.elements_action_selector'),
                                    '$".action-selector"'
                                ),
                            ],
                            [
                                new ResolvedAssertion(
                                    $assertionParser->parse('$elements.elements_assertion_selector exists'),
                                    '$".assertion-selector"'
                                ),
                            ]
                        ),
                    ])
                ),
            ],
            'empty step imports step, imported actions and assertions use inline data' => [
                'test' => $testParser->parse([
                    'step name' => [
                        'use' => 'step_import_name',
                        'data' => [
                            '0' => [
                                'key1' => 'key1value1',
                                'key2' => 'key2value1',
                            ],
                            '1' => [
                                'key1' => 'key1value2',
                                'key2' => 'key2value2',
                            ],
                        ],
                    ],
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new StepProvider([
                    'step_import_name' => $stepParser->parse([
                        'actions' => [
                            'set $".action-selector" to $data.key1'
                        ],
                        'assertions' => [
                            '$".assertion-selector" is $data.key2'
                        ],
                    ]),
                ]),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedTest' => $expectedResolvedDataTest,
            ],
            'empty step imports step, imported actions and assertions use imported data' => [
                'test' => $testParser->parse([
                    'step name' => [
                        'use' => 'step_import_name',
                        'data' => 'data_provider_import_name',
                    ],
                ]),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new StepProvider([
                    'step_import_name' => $stepParser->parse([
                        'actions' => [
                            'set $".action-selector" to $data.key1'
                        ],
                        'assertions' => [
                            '$".assertion-selector" is $data.key2'
                        ],
                    ]),
                ]),
                'dataSetProvider' => new DataSetProvider([
                    'data_provider_import_name' => new DataSetCollection([
                        '0' => [
                            'key1' => 'key1value1',
                            'key2' => 'key2value1',
                        ],
                        '1' => [
                            'key1' => 'key1value2',
                            'key2' => 'key2value2',
                        ],
                    ]),
                ]),
                'expectedTest' => $expectedResolvedDataTest,
            ],
            'deferred step import, imported actions and assertions require element resolution' => [
                'test' => $testParser->parse([
                    'step name' => [
                        'use' => 'step_import_name',
                        'elements' => [
                            'action_selector' => '$page_import_name.elements.action_selector',
                            'assertion_selector' => '$page_import_name.elements.assertion_selector',
                        ],
                    ],
                ]),
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page(
                        'page_import_name',
                        'http://example.com',
                        [
                            'action_selector' => '$".action-selector"',
                            'assertion_selector' => '$".assertion-selector"',
                        ]
                    ),
                ]),
                'stepProvider' => new StepProvider([
                    'step_import_name' => $stepParser->parse([
                        'use' => 'deferred',
                    ]),
                    'deferred' => $stepParser->parse([
                        'actions' => [
                            'click $elements.action_selector',
                        ],
                        'assertions' => [
                            '$elements.assertion_selector exists',
                        ],
                    ]),
                ]),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedTest' => new Test(
                    new Configuration('', ''),
                    new StepCollection([
                        'step name' => new Step(
                            [
                                new ResolvedAction(
                                    $actionParser->parse('click $elements.action_selector'),
                                    '$".action-selector"'
                                ),
                            ],
                            [
                                new ResolvedAssertion(
                                    $assertionParser->parse('$elements.assertion_selector exists'),
                                    '$".assertion-selector"'
                                ),
                            ]
                        ),
                    ])
                ),
            ],
            'deferred step import, imported actions and assertions use imported data' => [
                'test' => $testParser->parse([
                    'step name' => [
                        'use' => 'step_import_name',
                        'data' => 'data_provider_import_name',
                        'elements' => [
                            'action_selector' => '$page_import_name.elements.action_selector',
                            'assertion_selector' => '$page_import_name.elements.assertion_selector',
                        ],
                    ],
                ]),
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page(
                        'page_import_name',
                        'http://example.com',
                        [
                            'action_selector' => '$".action-selector"',
                            'assertion_selector' => '$".assertion-selector"',
                        ]
                    ),
                ]),
                'stepProvider' => new StepProvider([
                    'step_import_name' => $stepParser->parse([
                        'use' => 'deferred',
                    ]),
                    'deferred' => $stepParser->parse([
                        'actions' => [
                            'set $elements.action_selector to $data.key1',
                        ],
                        'assertions' => [
                            '$elements.assertion_selector is $data.key2',
                        ],
                    ]),
                ]),
                'dataSetProvider' => new DataSetProvider([
                    'data_provider_import_name' => new DataSetCollection([
                        '0' => [
                            'key1' => 'key1value1',
                            'key2' => 'key2value1',
                        ],
                        '1' => [
                            'key1' => 'key1value2',
                            'key2' => 'key2value2',
                        ],
                    ]),
                ]),
                'expectedTest' => new Test(
                    new Configuration('', ''),
                    new StepCollection([
                        'step name' => (new Step(
                            [
                                new ResolvedAction(
                                    $actionParser->parse('set $elements.action_selector to $data.key1'),
                                    '$".action-selector"',
                                    '$data.key1'
                                ),
                            ],
                            [
                                new ResolvedAssertion(
                                    $assertionParser->parse('$elements.assertion_selector is $data.key2'),
                                    '$".assertion-selector"',
                                    '$data.key2'
                                ),
                            ]
                        ))->withData(new DataSetCollection([
                            '0' => [
                                'key1' => 'key1value1',
                                'key2' => 'key2value1',
                            ],
                            '1' => [
                                'key1' => 'key1value2',
                                'key2' => 'key2value2',
                            ],
                        ])),
                    ])
                ),
            ],
        ];
    }

    /**
     * @dataProvider resolveThrowsExceptionDataProvider
     */
    public function testResolveThrowsException(
        TestInterface $test,
        PageProviderInterface $pageProvider,
        StepProviderInterface $stepProvider,
        DataSetProviderInterface $dataSetProvider,
        ContextAwareExceptionInterface $expectedException
    ): void {
        try {
            $this->resolver->resolve($test, $pageProvider, $stepProvider, $dataSetProvider);

            $this->fail('Exception not thrown');
        } catch (ContextAwareExceptionInterface $contextAwareException) {
            $this->assertEquals($expectedException, $contextAwareException);
        }
    }

    /**
     * @return array<mixed>
     */
    public function resolveThrowsExceptionDataProvider(): array
    {
        $testParser = TestParser::create();

        return [
            'UnknownDataProviderException: test.data references a data provider that has not been defined' => [
                'test' => $testParser->parse([
                    'step name' => [
                        'use' => 'step_import_name',
                        'data' => 'data_provider_import_name',
                    ],
                ])->withPath('test.yml'),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new StepProvider([
                    'step_import_name' => new Step([], []),
                ]),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => $this->applyContextToException(
                    new UnknownItemException(UnknownItemException::TYPE_DATASET, 'data_provider_import_name'),
                    [
                        ExceptionContextInterface::KEY_TEST_NAME => 'test.yml',
                        ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                    ]
                ),
            ],
            'UnknownPageException: config.url references page not defined within a collection' => [
                'test' => $testParser->parse([
                    'config' => [
                        'url' => '$page_import_name.url',
                    ],
                ])->withPath('test.yml'),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => $this->applyContextToException(
                    new UnknownItemException(UnknownItemException::TYPE_PAGE, 'page_import_name'),
                    [
                        ExceptionContextInterface::KEY_TEST_NAME => 'test.yml',
                    ]
                ),
            ],
            'UnknownPageException: assertion string references page not defined within a collection' => [
                'test' => $testParser->parse([
                    'step name' => [
                        'assertions' => [
                            '$page_import_name.elements.element_name exists'
                        ],
                    ],
                ])->withPath('test.yml'),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => $this->applyContextToException(
                    new UnknownItemException(UnknownItemException::TYPE_PAGE, 'page_import_name'),
                    [
                        ExceptionContextInterface::KEY_TEST_NAME => 'test.yml',
                        ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                        ExceptionContextInterface::KEY_CONTENT => '$page_import_name.elements.element_name exists',
                    ]
                ),
            ],
            'UnknownPageException: action string references page not defined within a collection' => [
                'test' => $testParser->parse([
                    'step name' => [
                        'actions' => [
                            'click $page_import_name.elements.element_name'
                        ],
                    ],
                ])->withPath('test.yml'),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => $this->applyContextToException(
                    new UnknownItemException(UnknownItemException::TYPE_PAGE, 'page_import_name'),
                    [
                        ExceptionContextInterface::KEY_TEST_NAME => 'test.yml',
                        ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                        ExceptionContextInterface::KEY_CONTENT => 'click $page_import_name.elements.element_name',
                    ]
                ),
            ],
            'UnknownPageElementException: test.elements references element that does not exist within a page' => [
                'test' => $testParser->parse([
                    'step name' => [
                        'elements' => [
                            'non_existent' => '$page_import_name.elements.non_existent',
                        ],
                    ],
                ])->withPath('test.yml'),
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page('page_import_name', 'http://example.com')
                ]),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => $this->applyContextToException(
                    new UnknownPageElementException('page_import_name', 'non_existent'),
                    [
                        ExceptionContextInterface::KEY_TEST_NAME => 'test.yml',
                        ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                    ]
                ),
            ],
            'UnknownPageElementException: assertion string references element that does not exist within a page' => [
                'test' => $testParser->parse([
                    'step name' => [
                        'assertions' => [
                            '$page_import_name.elements.non_existent exists',
                        ],
                    ],
                ])->withPath('test.yml'),
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page('page_import_name', 'http://example.com')
                ]),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => $this->applyContextToException(
                    new UnknownPageElementException('page_import_name', 'non_existent'),
                    [
                        ExceptionContextInterface::KEY_TEST_NAME => 'test.yml',
                        ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                        ExceptionContextInterface::KEY_CONTENT => '$page_import_name.elements.non_existent exists',
                    ]
                ),
            ],
            'UnknownPageElementException: action string references element that does not exist within a page' => [
                'test' => $testParser->parse([
                    'step name' => [
                        'actions' => [
                            'click $page_import_name.elements.non_existent',
                        ],
                    ],
                ])->withPath('test.yml'),
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page('page_import_name', 'http://example.com')
                ]),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => $this->applyContextToException(
                    new UnknownPageElementException('page_import_name', 'non_existent'),
                    [
                        ExceptionContextInterface::KEY_TEST_NAME => 'test.yml',
                        ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                        ExceptionContextInterface::KEY_CONTENT => 'click $page_import_name.elements.non_existent',
                    ]
                ),
            ],
            'UnknownStepException: step.use references step not defined within a collection' => [
                'test' => $testParser->parse([
                    'step name' => [
                        'use' => 'step_import_name',
                    ],
                ])->withPath('test.yml'),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => $this->applyContextToException(
                    new UnknownItemException(UnknownItemException::TYPE_STEP, 'step_import_name'),
                    [
                        ExceptionContextInterface::KEY_TEST_NAME => 'test.yml',
                        ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                    ]
                ),
            ],
            'UnknownElementException: action element parameter references unknown step element' => [
                'test' => $testParser->parse([
                    'step name' => [
                        'actions' => [
                            'click $elements.element_name',
                        ],
                    ],
                ])->withPath('test.yml'),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => $this->applyContextToException(
                    new UnknownElementException('element_name'),
                    [
                        ExceptionContextInterface::KEY_TEST_NAME => 'test.yml',
                        ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                        ExceptionContextInterface::KEY_CONTENT => 'click $elements.element_name',
                    ]
                ),
            ],
            'UnknownElementException: assertion element parameter references unknown step element' => [
                'test' => $testParser->parse([
                    'step name' => [
                        'assertions' => [
                            '$elements.element_name exists',
                        ],
                    ],
                ])->withPath('test.yml'),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => $this->applyContextToException(
                    new UnknownElementException('element_name'),
                    [
                        ExceptionContextInterface::KEY_TEST_NAME => 'test.yml',
                        ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                        ExceptionContextInterface::KEY_CONTENT => '$elements.element_name exists',
                    ]
                )
            ],
        ];
    }

    /**
     * @param array<string, string> $context
     */
    private function applyContextToException(
        ContextAwareExceptionInterface $contextAwareException,
        array $context
    ): ContextAwareExceptionInterface {
        $contextAwareException->applyExceptionContext($context);

        return $contextAwareException;
    }
}
