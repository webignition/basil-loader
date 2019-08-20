<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Builder;

use Nyholm\Psr7\Uri;
use webignition\BasilDataStructure\PathResolver;
use webignition\BasilModel\Action\WaitAction;
use webignition\BasilModel\Assertion\Assertion;
use webignition\BasilModel\Assertion\AssertionComparisons;
use webignition\BasilModel\DataSet\DataSet;
use webignition\BasilModel\DataSet\DataSetCollection;
use webignition\BasilModel\Identifier\IdentifierCollection;
use webignition\BasilModel\Page\Page;
use webignition\BasilModel\Step\Step;
use webignition\BasilModel\Test\Configuration;
use webignition\BasilModel\Test\Test;
use webignition\BasilModel\Test\TestInterface;
use webignition\BasilModel\Value\ElementValue;
use webignition\BasilModel\Value\LiteralValue;
use webignition\BasilModelProvider\DataSet\DataSetProvider;
use webignition\BasilModelProvider\DataSet\DataSetProviderInterface;
use webignition\BasilModelProvider\Page\PageProvider;
use webignition\BasilModelProvider\Page\PageProviderInterface;
use webignition\BasilModelProvider\Step\StepProvider;
use webignition\BasilModelProvider\Step\StepProviderInterface;
use webignition\BasilParser\Builder\TestBuilder;
use webignition\BasilDataStructure\Step as StepData;
use webignition\BasilDataStructure\Test\Configuration as ConfigurationData;
use webignition\BasilDataStructure\Test\Imports as ImportsData;
use webignition\BasilDataStructure\Test\Test as TestData;
use webignition\BasilParser\Tests\Services\Provider\EmptyDataSetProvider;
use webignition\BasilParser\Tests\Services\Provider\EmptyPageProvider;
use webignition\BasilParser\Tests\Services\Provider\EmptyStepProvider;
use webignition\BasilParser\Tests\Services\TestIdentifierFactory;

class TestBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TestBuilder
     */
    private $testBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testBuilder = TestBuilder::createBuilder();
    }

    /**
     * @dataProvider buildDataProvider
     */
    public function testBuild(
        TestData $testData,
        PageProviderInterface $pageProvider,
        StepProviderInterface $stepProvider,
        DataSetProviderInterface $dataSetProvider,
        TestInterface $expectedTest
    ) {
        $test = $this->testBuilder->build($testData, $pageProvider, $stepProvider, $dataSetProvider);

        $this->assertEquals($expectedTest, $test);
    }

    public function buildDataProvider(): array
    {
        return [
            'literal steps, no imports, no resolution required' => [
                'testData' => new TestData(
                    PathResolver::create(),
                    [
                        TestData::KEY_CONFIGURATION => [
                            ConfigurationData::KEY_BROWSER => 'chrome',
                            ConfigurationData::KEY_URL => 'http://example.com/',
                        ],
                        'step name' => [
                            StepData::KEY_ACTIONS => [
                                'wait 30',
                            ],
                            StepData::KEY_ASSERTIONS => [
                                '".selector" exists',
                            ],
                        ],
                    ],
                    '/path/to/test.yml'
                ),
                'pageProvider' => new EmptyPageProvider(),
                'stepProvider' => new EmptyStepProvider(),
                'dataSetProvider' => new EmptyDataSetProvider(),
                'expectedTest' => new Test(
                    '/path/to/test.yml',
                    new Configuration('chrome', 'http://example.com/'),
                    [
                        'step name' => new Step(
                            [
                                new WaitAction('wait 30', LiteralValue::createStringValue('30')),
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
                    ]
                ),
            ],
            'imported step' => [
                'testData' => new TestData(
                    PathResolver::create(),
                    [
                        TestData::KEY_CONFIGURATION => [
                            ConfigurationData::KEY_BROWSER => 'chrome',
                            ConfigurationData::KEY_URL => 'http://example.com/',
                        ],
                        TestData::KEY_IMPORTS => [
                            ImportsData::KEY_PAGES => [
                                'page_import_name' => '/path/to/page.yml',
                            ],
                            ImportsData::KEY_STEPS => [
                                'step_import_name' => '/path/to/step.yml',
                            ],
                            ImportsData::KEY_DATA_PROVIDERS => [
                                'data_provider_import_name' => '/path/to/data_set.yml',
                            ],
                        ],
                        'step referencing imported page element' => [
                            StepData::KEY_ASSERTIONS => [
                                'page_import_name.elements.element_name exists',
                            ],
                        ],
                        'step referencing imported step with imported data provider' => [
                            StepData::KEY_USE => 'step_import_name',
                            StepData::KEY_DATA => 'data_provider_import_name',
                            StepData::KEY_ELEMENTS => [
                                'element_name' => 'page_import_name.elements.element_name',
                            ],
                        ],
                    ],
                    '/path/to/test.yml'
                ),
                'pageProvider' => new PageProvider([
                    'page_import_name' => new Page(
                        new Uri('http://example.com/'),
                        new IdentifierCollection([
                            TestIdentifierFactory::createCssElementIdentifier(
                                '.imported-page-element-selector',
                                1,
                                'element_name'
                            ),
                        ])
                    )
                ]),
                'stepProvider' => new StepProvider([
                    'step_import_name' => new Step(
                        [
                            new WaitAction('wait 10', LiteralValue::createStringValue('10')),
                        ],
                        [
                            new Assertion(
                                '".imported-step-selector" exists',
                                new ElementValue(
                                    TestIdentifierFactory::createCssElementIdentifier(
                                        '.imported-page-element-selector',
                                        1,
                                        'element_name'
                                    )
                                ),
                                AssertionComparisons::EXISTS
                            )
                        ]
                    )
                ]),
                'dataSetProvider' => new DataSetProvider([
                    'data_provider_import_name' => new DataSetCollection([
                        new DataSet('0', [
                            'foo' => 'bar',
                        ])
                    ]),
                ]),
                'expectedTest' => new Test(
                    '/path/to/test.yml',
                    new Configuration('chrome', 'http://example.com/'),
                    [
                        'step referencing imported page element' => new Step(
                            [],
                            [
                                new Assertion(
                                    'page_import_name.elements.element_name exists',
                                    new ElementValue(
                                        TestIdentifierFactory::createCssElementIdentifier(
                                            '.imported-page-element-selector',
                                            1,
                                            'element_name'
                                        )
                                    ),
                                    AssertionComparisons::EXISTS
                                )
                            ]
                        ),
                        'step referencing imported step with imported data provider' => (new Step(
                            [
                                new WaitAction('wait 10', LiteralValue::createStringValue('10')),
                            ],
                            [
                                new Assertion(
                                    '".imported-step-selector" exists',
                                    new ElementValue(
                                        TestIdentifierFactory::createCssElementIdentifier(
                                            '.imported-page-element-selector',
                                            1,
                                            'element_name'
                                        )
                                    ),
                                    AssertionComparisons::EXISTS
                                )
                            ]
                        ))->withDataSetCollection(new DataSetCollection([
                            new DataSet('0', [
                                'foo' => 'bar',
                            ])
                        ])),
                    ]
                ),
            ],
        ];
    }
}
