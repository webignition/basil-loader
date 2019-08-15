<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Resolver\Test;

use Nyholm\Psr7\Uri;
use webignition\BasilContextAwareException\ContextAwareExceptionInterface;
use webignition\BasilContextAwareException\ExceptionContext\ExceptionContext;
use webignition\BasilContextAwareException\ExceptionContext\ExceptionContextInterface;
use webignition\BasilModel\Action\ActionTypes;
use webignition\BasilModel\Action\InteractionAction;
use webignition\BasilModel\Assertion\Assertion;
use webignition\BasilModel\Assertion\AssertionComparisons;
use webignition\BasilModel\DataSet\DataSet;
use webignition\BasilModel\DataSet\DataSetCollection;
use webignition\BasilModel\Identifier\Identifier;
use webignition\BasilModel\Identifier\IdentifierCollection;
use webignition\BasilModel\Identifier\IdentifierTypes;
use webignition\BasilModel\Page\Page;
use webignition\BasilModel\Step\PendingImportResolutionStep;
use webignition\BasilModel\Step\Step;
use webignition\BasilModel\Test\Configuration;
use webignition\BasilModel\Test\Test;
use webignition\BasilModel\Test\TestInterface;
use webignition\BasilModel\Value\ElementValue;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilModelFactory\Action\ActionFactory;
use webignition\BasilModelFactory\AssertionFactory;
use webignition\BasilModelFactory\InvalidPageElementIdentifierException;
use webignition\BasilParser\Exception\NonRetrievableDataProviderException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\NonRetrievableStepException;
use webignition\BasilParser\Exception\UnknownDataProviderException;
use webignition\BasilParser\Exception\UnknownElementException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Exception\UnknownStepException;
use webignition\BasilParser\Provider\DataSet\DataSetProviderInterface;
use webignition\BasilParser\Provider\DataSet\EmptyDataSetProvider;
use webignition\BasilParser\Provider\DataSet\PopulatedDataSetProvider;
use webignition\BasilParser\Provider\Page\EmptyPageProvider;
use webignition\BasilParser\Provider\Page\PageProviderInterface;
use webignition\BasilParser\Provider\Page\PopulatedPageProvider;
use webignition\BasilParser\Provider\Step\EmptyStepProvider;
use webignition\BasilParser\Provider\Step\PopulatedStepProvider;
use webignition\BasilParser\Provider\Step\StepProviderInterface;
use webignition\BasilParser\Resolver\Test\TestResolver;
use webignition\BasilParser\Tests\Services\FixturePathFinder;
use webignition\BasilParser\Provider\DataSet\Factory as DataSetProviderFactory;
use webignition\BasilParser\Provider\Page\Factory as PageProviderFactory;
use webignition\BasilParser\Provider\Step\Factory as StepProviderFactory;
use webignition\BasilParser\Tests\Services\TestIdentifierFactory;

class TestResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TestResolver
     */
    private $resolver;

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
    ) {
        $resolvedTest = $this->resolver->resolve($test, $pageProvider, $stepProvider, $dataSetProvider);

        $this->assertEquals($expectedTest, $resolvedTest);
    }

    public function resolveSuccessDataProvider(): array
    {
        return [
            'empty' => [
                'test' => new Test('test name', new Configuration('', ''), []),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedTest' => new Test('test name', new Configuration('', ''), []),
            ],
            'configuration is resolved' => [
                'test' => new Test(
                    'test name',
                    new Configuration('', 'page_import_name.url'),
                    []
                ),
                'pageProvider' => new PopulatedPageProvider([
                    'page_import_name' => new Page(new Uri('http://example.com/'), new IdentifierCollection()),
                ]),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedTest' => new Test(
                    'test name',
                    new Configuration('', 'http://example.com/'),
                    []
                ),
            ],
            'steps are resolved' => [
                'test' => new Test(
                    'test name',
                    new Configuration('', ''),
                    [
                        'step name' => (new PendingImportResolutionStep(
                            new Step([], []),
                            'step_import_name',
                            'data_provider_import_name'
                        ))->withIdentifierCollection(new IdentifierCollection([
                            TestIdentifierFactory::createPageElementReferenceIdentifier(
                                new ObjectValue(
                                    ValueTypes::PAGE_ELEMENT_REFERENCE,
                                    'page_import_name.elements.heading_element_name',
                                    'page_import_name',
                                    'heading_element_name'
                                ),
                                'heading'
                            ),
                        ])),
                    ]
                ),
                'pageProvider' => new PopulatedPageProvider([
                    'page_import_name' => new Page(
                        new Uri('http://example.com/'),
                        new IdentifierCollection([
                            TestIdentifierFactory::createCssElementIdentifier(
                                '.action-selector',
                                1,
                                'action_element_name'
                            ),
                            TestIdentifierFactory::createCssElementIdentifier(
                                '.assertion-selector',
                                1,
                                'assertion_element_name'
                            ),
                            TestIdentifierFactory::createCssElementIdentifier(
                                '.heading-selector',
                                1,
                                'heading_element_name'
                            ),
                        ])
                    )
                ]),
                'stepProvider' => new PopulatedStepProvider([
                    'step_import_name' => new Step(
                        [
                            new InteractionAction(
                                'click page_import_name.elements.action_element_name',
                                ActionTypes::CLICK,
                                new Identifier(
                                    IdentifierTypes::PAGE_ELEMENT_REFERENCE,
                                    new ObjectValue(
                                        ValueTypes::PAGE_ELEMENT_REFERENCE,
                                        'page_import_name.elements.action_element_name',
                                        'page_import_name',
                                        'action_element_name'
                                    )
                                ),
                                'page_import_name.elements.action_element_name'
                            )
                        ],
                        [
                            new Assertion(
                                'page_import_name.elements.assertion_element_name exists',
                                new ObjectValue(
                                    ValueTypes::PAGE_ELEMENT_REFERENCE,
                                    'page_import_name.elements.assertion_element_name',
                                    'page_import_name',
                                    'assertion_element_name'
                                ),
                                AssertionComparisons::EXISTS
                            )
                        ]
                    ),
                ]),
                'dataSetProvider' => new PopulatedDataSetProvider([
                    'data_provider_import_name' => new DataSetCollection([
                        new DataSet('0', [
                            'foo' => 'bar',
                        ])
                    ]),
                ]),
                'expectedTest' => new Test(
                    'test name',
                    new Configuration('', ''),
                    [
                        'step name' => (new Step(
                            [
                                new InteractionAction(
                                    'click page_import_name.elements.action_element_name',
                                    ActionTypes::CLICK,
                                    TestIdentifierFactory::createCssElementIdentifier(
                                        '.action-selector',
                                        1,
                                        'action_element_name'
                                    ),
                                    'page_import_name.elements.action_element_name'
                                )
                            ],
                            [
                                new Assertion(
                                    'page_import_name.elements.assertion_element_name exists',
                                    new ElementValue(
                                        TestIdentifierFactory::createCssElementIdentifier(
                                            '.assertion-selector',
                                            1,
                                            'assertion_element_name'
                                        )
                                    ),
                                    AssertionComparisons::EXISTS
                                )
                            ]
                        ))->withDataSetCollection(new DataSetCollection([
                            new DataSet('0', [
                                'foo' => 'bar',
                            ]),
                        ]))->withIdentifierCollection(new IdentifierCollection([
                            TestIdentifierFactory::createCssElementIdentifier(
                                '.heading-selector',
                                1,
                                'heading_element_name'
                            ),
                        ])),
                    ]
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
        string $expectedException,
        string $expectedExceptionMessage,
        ExceptionContext $expectedExceptionContext
    ) {
        try {
            $this->resolver->resolve($test, $pageProvider, $stepProvider, $dataSetProvider);
        } catch (ContextAwareExceptionInterface $contextAwareException) {
            $this->assertInstanceOf($expectedException, $contextAwareException);
            $this->assertEquals($expectedExceptionMessage, $contextAwareException->getMessage());
            $this->assertEquals($expectedExceptionContext, $contextAwareException->getExceptionContext());
        }
    }

    public function resolveThrowsExceptionDataProvider(): array
    {
        $invalidYamlPath = FixturePathFinder::find('invalid-yaml.yml');

        return [
            'NonRetrievableDataProviderException: test.data references data provider that cannot be loaded' => [
                'test' => new Test(
                    'test name',
                    new Configuration('chrome', 'http://example.com'),
                    [
                        'step name' => new PendingImportResolutionStep(
                            new Step([], []),
                            '',
                            'data_provider_name'
                        )
                    ]
                ),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => DataSetProviderFactory::createFactory()->createDeferredDataSetProvider([
                    'data_provider_name' => 'DataProvider/non-existent.yml',
                ]),
                'expectedException' => NonRetrievableDataProviderException::class,
                'expectedExceptionMessage' =>
                    'Cannot retrieve data provider "data_provider_name" from "DataProvider/non-existent.yml"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                ])
            ],
            'NonRetrievableDataProviderException: test.data references data provider containing invalid yaml' => [
                'test' => new Test(
                    'test name',
                    new Configuration('chrome', 'http://example.com'),
                    [
                        'step name' => new PendingImportResolutionStep(
                            new Step([], []),
                            '',
                            'data_provider_name'
                        )
                    ]
                ),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => DataSetProviderFactory::createFactory()->createDeferredDataSetProvider([
                    'data_provider_name' => $invalidYamlPath,
                ]),
                'expectedException' => NonRetrievableDataProviderException::class,
                'expectedExceptionMessage' =>
                    'Cannot retrieve data provider "data_provider_name" from "' . $invalidYamlPath . '"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                ])
            ],
            'NonRetrievablePageException: config.url references page that does not exist' => [
                'test' => new Test(
                    'test name',
                    new Configuration('chrome', 'page_import_name.url'),
                    []
                ),
                'pageProvider' => PageProviderFactory::createFactory()->createDeferredPageProvider([
                    'page_import_name' => 'Page/non-existent.yml',
                ]),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => NonRetrievablePageException::class,
                'expectedExceptionMessage' => 'Cannot retrieve page "page_import_name" from "Page/non-existent.yml"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                ])
            ],
            'NonRetrievablePageException: config.url references page that contains invalid yaml' => [
                'test' => new Test(
                    'test name',
                    new Configuration('chrome', 'page_import_name.url'),
                    []
                ),
                'pageProvider' => PageProviderFactory::createFactory()->createDeferredPageProvider([
                    'page_import_name' => $invalidYamlPath,
                ]),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => NonRetrievablePageException::class,
                'expectedExceptionMessage' =>
                    'Cannot retrieve page "page_import_name" from "' . $invalidYamlPath . '"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                ])
            ],
            'NonRetrievablePageException: assertion string references page that does not exist' => [
                'test' => new Test(
                    'test name',
                    new Configuration('chrome', 'http://example.com'),
                    [
                        'step name' => new Step(
                            [],
                            [
                                (AssertionFactory::createFactory())
                                    ->createFromAssertionString('page_import_name.elements.element_name exists')
                            ]
                        )
                    ]
                ),
                'pageProvider' => PageProviderFactory::createFactory()->createDeferredPageProvider([
                    'page_import_name' => 'Page/non-existent.yml',
                ]),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => NonRetrievablePageException::class,
                'expectedExceptionMessage' => 'Cannot retrieve page "page_import_name" from "Page/non-existent.yml"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                    ExceptionContextInterface::KEY_CONTENT => 'page_import_name.elements.element_name exists',
                ])
            ],
            'NonRetrievablePageException: assertion string references page that contains invalid yaml' => [
                'test' => new Test(
                    'test name',
                    new Configuration('chrome', 'http://example.com'),
                    [
                        'step name' => new Step(
                            [],
                            [
                                (AssertionFactory::createFactory())
                                    ->createFromAssertionString('page_import_name.elements.element_name exists')
                            ]
                        )
                    ]
                ),
                'pageProvider' => PageProviderFactory::createFactory()->createDeferredPageProvider([
                    'page_import_name' => $invalidYamlPath,
                ]),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => NonRetrievablePageException::class,
                'expectedExceptionMessage' =>
                    'Cannot retrieve page "page_import_name" from "' . $invalidYamlPath . '"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                    ExceptionContextInterface::KEY_CONTENT => 'page_import_name.elements.element_name exists',
                ])
            ],
            'NonRetrievablePageException: action string references page that does not exist' => [
                'test' => new Test(
                    'test name',
                    new Configuration('chrome', 'http://example.com'),
                    [
                        'step name' => new Step(
                            [
                                (ActionFactory::createFactory())
                                    ->createFromActionString('click page_import_name.elements.element_name')
                            ],
                            []
                        )
                    ]
                ),
                'pageProvider' => PageProviderFactory::createFactory()->createDeferredPageProvider([
                    'page_import_name' => 'Page/non-existent.yml',
                ]),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => NonRetrievablePageException::class,
                'expectedExceptionMessage' => 'Cannot retrieve page "page_import_name" from "Page/non-existent.yml"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                    ExceptionContextInterface::KEY_CONTENT => 'click page_import_name.elements.element_name',
                ])
            ],
            'NonRetrievablePageException: action string references page that contains invalid yaml' => [
                'test' => new Test(
                    'test name',
                    new Configuration('chrome', 'http://example.com'),
                    [
                        'step name' => new Step(
                            [
                                (ActionFactory::createFactory())
                                    ->createFromActionString('click page_import_name.elements.element_name')
                            ],
                            []
                        )
                    ]
                ),
                'pageProvider' => PageProviderFactory::createFactory()->createDeferredPageProvider([
                    'page_import_name' => $invalidYamlPath,
                ]),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => NonRetrievablePageException::class,
                'expectedExceptionMessage' =>
                    'Cannot retrieve page "page_import_name" from "' . $invalidYamlPath . '"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                    ExceptionContextInterface::KEY_CONTENT => 'click page_import_name.elements.element_name',
                ])
            ],
            'NonRetrievableStepException: step.uses references step that does not exist' => [
                'test' => new Test(
                    'test name',
                    new Configuration('chrome', 'http://example.com'),
                    [
                        'step name' => new PendingImportResolutionStep(
                            new Step([], []),
                            'step_import_name',
                            ''
                        )
                    ]
                ),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => StepProviderFactory::createFactory()->createDeferredStepProvider([
                    'step_import_name' => 'Step/non-existent.yml',
                ]),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => NonRetrievableStepException::class,
                'expectedExceptionMessage' => 'Cannot retrieve step "step_import_name" from "Step/non-existent.yml"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                ])
            ],
            'NonRetrievableStepException: step.uses references step contains invalid yaml' => [
                'test' => new Test(
                    'test name',
                    new Configuration('chrome', 'http://example.com'),
                    [
                        'step name' => new PendingImportResolutionStep(
                            new Step([], []),
                            'step_import_name',
                            ''
                        )
                    ]
                ),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => StepProviderFactory::createFactory()->createDeferredStepProvider([
                    'step_import_name' => $invalidYamlPath,
                ]),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => NonRetrievableStepException::class,
                'expectedExceptionMessage' =>
                    'Cannot retrieve step "step_import_name" from "' . $invalidYamlPath . '"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                ])
            ],
            'UnknownDataProviderException: test.data references a data provider that has not been defined' => [
                'test' => new Test(
                    'test name',
                    new Configuration('chrome', 'http://example.com'),
                    [
                        'step name' => new PendingImportResolutionStep(
                            new Step([], []),
                            'step_import_name',
                            'data_provider_import_name'
                        )
                    ]
                ),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new PopulatedStepProvider([
                    'step_import_name' => new Step([], []),
                ]),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => UnknownDataProviderException::class,
                'expectedExceptionMessage' => 'Unknown data provider "data_provider_import_name"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                ])
            ],
            'UnknownPageException: config.url references page not defined within a collection' => [
                'test' => new Test(
                    'test name',
                    new Configuration('chrome', 'page_import_name.url'),
                    []
                ),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => UnknownPageException::class,
                'expectedExceptionMessage' => 'Unknown page "page_import_name"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                ])
            ],
            'UnknownPageException: assertion string references page not defined within a collection' => [
                'test' => new Test(
                    'test name',
                    new Configuration('chrome', 'http://example.com'),
                    [
                        'step name' => new Step(
                            [],
                            [
                                (AssertionFactory::createFactory())
                                    ->createFromAssertionString('page_import_name.elements.element_name exists'),
                            ]
                        )
                    ]
                ),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => UnknownPageException::class,
                'expectedExceptionMessage' => 'Unknown page "page_import_name"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                    ExceptionContextInterface::KEY_CONTENT => 'page_import_name.elements.element_name exists',
                ])
            ],
            'UnknownPageException: action string references page not defined within a collection' => [
                'test' => new Test(
                    'test name',
                    new Configuration('chrome', 'http://example.com'),
                    [
                        'step name' => new Step(
                            [
                                (ActionFactory::createFactory())
                                    ->createFromActionString('click page_import_name.elements.element_name')
                            ],
                            []
                        )
                    ]
                ),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => UnknownPageException::class,
                'expectedExceptionMessage' => 'Unknown page "page_import_name"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                    ExceptionContextInterface::KEY_CONTENT => 'click page_import_name.elements.element_name',
                ])
            ],
            'UnknownPageElementException: test.elements references element that does not exist within a page' => [
                'test' => new Test(
                    'test name',
                    new Configuration('chrome', 'http://example.com'),
                    [
                        'step name' => (new Step([], []))->withIdentifierCollection(new IdentifierCollection([
                            TestIdentifierFactory::createPageElementReferenceIdentifier(
                                new ObjectValue(
                                    ValueTypes::PAGE_ELEMENT_REFERENCE,
                                    'page_import_name.elements.non_existent',
                                    'page_import_name',
                                    'non_existent'
                                ),
                                'non_existent'
                            ),
                        ])),
                    ]
                ),
                'pageProvider' => new PopulatedPageProvider([
                    'page_import_name' => new Page(
                        new Uri('http://example.com'),
                        new IdentifierCollection()
                    )
                ]),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => UnknownPageElementException::class,
                'expectedExceptionMessage' => 'Unknown page element "non_existent" in page "page_import_name"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                ])
            ],
            'UnknownPageElementException: assertion string references element that does not exist within a page' => [
                'test' => new Test(
                    'test name',
                    new Configuration('chrome', 'http://example.com'),
                    [
                        'step name' => new Step(
                            [],
                            [
                                (AssertionFactory::createFactory())
                                    ->createFromAssertionString('page_import_name.elements.non_existent exists'),
                            ]
                        ),
                    ]
                ),
                'pageProvider' => new PopulatedPageProvider([
                    'page_import_name' => new Page(
                        new Uri('http://example.com'),
                        new IdentifierCollection()
                    )
                ]),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => UnknownPageElementException::class,
                'expectedExceptionMessage' => 'Unknown page element "non_existent" in page "page_import_name"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                    ExceptionContextInterface::KEY_CONTENT => 'page_import_name.elements.non_existent exists',
                ])
            ],
            'UnknownPageElementException: action string references element that does not exist within a page' => [
                'test' => new Test(
                    'test name',
                    new Configuration('chrome', 'http://example.com'),
                    [
                        'step name' => new Step(
                            [
                                (ActionFactory::createFactory())
                                    ->createFromActionString('click page_import_name.elements.non_existent')
                            ],
                            []
                        ),
                    ]
                ),
                'pageProvider' => new PopulatedPageProvider([
                    'page_import_name' => new Page(
                        new Uri('http://example.com'),
                        new IdentifierCollection()
                    )
                ]),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => UnknownPageElementException::class,
                'expectedExceptionMessage' => 'Unknown page element "non_existent" in page "page_import_name"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                    ExceptionContextInterface::KEY_CONTENT => 'click page_import_name.elements.non_existent',
                ])
            ],
            'UnknownStepException: step.use references step not defined within a collection' => [
                'test' => new Test(
                    'test name',
                    new Configuration('chrome', 'http://example.com'),
                    [
                        'step name' => new PendingImportResolutionStep(
                            new Step([], []),
                            'step_import_name',
                            ''
                        ),
                    ]
                ),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => UnknownStepException::class,
                'expectedExceptionMessage' => 'Unknown step "step_import_name"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                ])
            ],
            'InvalidPageElementIdentifierException: action string references invalid element identifier in page' => [
                'test' => new Test(
                    'test name',
                    new Configuration('chrome', 'http://example.com'),
                    [
                        'step name' => new Step(
                            [
                                (ActionFactory::createFactory())
                                    ->createFromActionString('click page_import_name.elements.element_name')
                            ],
                            []
                        ),
                    ]
                ),
                'pageProvider' => PageProviderFactory::createFactory()->createDeferredPageProvider([
                    'page_import_name' => FixturePathFinder::find('Page/example.com.non-elemental-identifier.yml'),
                ]),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => InvalidPageElementIdentifierException::class,
                'expectedExceptionMessage' => 'Invalid page element identifier "".selector".attribute_name"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                    ExceptionContextInterface::KEY_CONTENT => 'click page_import_name.elements.element_name',
                ])
            ],
            'InvalidPageElementIdentifierException: assertion string references invalid element identifier in page' => [
                'test' => new Test(
                    'test name',
                    new Configuration('chrome', 'http://example.com'),
                    [
                        'step name' => new Step(
                            [],
                            [
                                (AssertionFactory::createFactory())
                                    ->createFromAssertionString('page_import_name.elements.element_name exists'),
                            ]
                        ),
                    ]
                ),
                'pageProvider' => PageProviderFactory::createFactory()->createDeferredPageProvider([
                    'page_import_name' => FixturePathFinder::find('Page/example.com.non-elemental-identifier.yml'),
                ]),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => InvalidPageElementIdentifierException::class,
                'expectedExceptionMessage' => 'Invalid page element identifier "".selector".attribute_name"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                    ExceptionContextInterface::KEY_CONTENT => 'page_import_name.elements.element_name exists',
                ])
            ],
            'UnknownElementException: action element parameter references unknown step element' => [
                'test' => new Test(
                    'test name',
                    new Configuration('chrome', 'http://example.com'),
                    [
                        'step name' => new Step(
                            [
                                (ActionFactory::createFactory())
                                    ->createFromActionString('click $elements.element_name')
                            ],
                            []
                        ),
                    ]
                ),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => UnknownElementException::class,
                'expectedExceptionMessage' => 'Unknown element "element_name"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                    ExceptionContextInterface::KEY_CONTENT => 'click $elements.element_name',
                ])
            ],
            'UnknownElementException: assertion element parameter references unknown step element' => [
                'test' => new Test(
                    'test name',
                    new Configuration('chrome', 'http://example.com'),
                    [
                        'step name' => new Step(
                            [],
                            [
                                (AssertionFactory::createFactory())
                                    ->createFromAssertionString('$elements.element_name exists'),
                            ]
                        ),
                    ]
                ),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedException' => UnknownElementException::class,
                'expectedExceptionMessage' => 'Unknown element "element_name"',
                'expectedExceptionContext' =>  new ExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => 'test name',
                    ExceptionContextInterface::KEY_STEP_NAME => 'step name',
                    ExceptionContextInterface::KEY_CONTENT => '$elements.element_name exists',
                ])
            ],
        ];
    }
}
