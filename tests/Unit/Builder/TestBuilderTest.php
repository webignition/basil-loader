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
use webignition\BasilModel\Identifier\Identifier;
use webignition\BasilModel\Identifier\IdentifierTypes;
use webignition\BasilModel\Page\Page;
use webignition\BasilModel\Step\Step;
use webignition\BasilModel\Test\Configuration;
use webignition\BasilModel\Test\Test;
use webignition\BasilModel\Test\TestInterface;
use webignition\BasilModel\Value\Value;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilParser\Builder\TestBuilder;
use webignition\BasilDataStructure\Step as StepData;
use webignition\BasilDataStructure\Test\Configuration as ConfigurationData;
use webignition\BasilDataStructure\Test\Imports as ImportsData;
use webignition\BasilDataStructure\Test\Test as TestData;
use webignition\BasilParser\Provider\DataSet\DataSetProviderInterface;
use webignition\BasilParser\Provider\DataSet\EmptyDataSetProvider;
use webignition\BasilParser\Provider\DataSet\PopulatedDataSetProvider;
use webignition\BasilParser\Provider\Page\EmptyPageProvider;
use webignition\BasilParser\Provider\Page\PageProviderInterface;
use webignition\BasilParser\Provider\Page\PopulatedPageProvider;
use webignition\BasilParser\Provider\Step\EmptyStepProvider;
use webignition\BasilParser\Provider\Step\PopulatedStepProvider;
use webignition\BasilParser\Provider\Step\StepProviderInterface;
use webignition\BasilParser\Tests\Services\TestBuilderFactory;

class TestBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TestBuilder
     */
    private $testBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testBuilder = TestBuilderFactory::create();
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
                                new WaitAction('wait 30', '30'),
                            ],
                            [
                                new Assertion(
                                    '".selector" exists',
                                    new Identifier(
                                        IdentifierTypes::CSS_SELECTOR,
                                        new Value(
                                            ValueTypes::STRING,
                                            '.selector'
                                        )
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
                'pageProvider' => new PopulatedPageProvider([
                    'page_import_name' => new Page(
                        new Uri('http://example.com/'),
                        [
                            'element_name' => new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                new Value(
                                    ValueTypes::STRING,
                                    '.imported-page-element-selector'
                                )
                            )
                        ]
                    )
                ]),
                'stepProvider' => new PopulatedStepProvider([
                    'step_import_name' => new Step(
                        [
                            new WaitAction('wait 10', '10'),
                        ],
                        [
                            new Assertion(
                                '".imported-step-selector" exists',
                                new Identifier(
                                    IdentifierTypes::CSS_SELECTOR,
                                    new Value(
                                        ValueTypes::STRING,
                                        '.imported-step-selector'
                                    )
                                ),
                                AssertionComparisons::EXISTS
                            )
                        ]
                    )
                ]),
                'dataSetProvider' => new PopulatedDataSetProvider([
                    'data_provider_import_name' => new DataSetCollection([
                        new DataSet([
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
                                    new Identifier(
                                        IdentifierTypes::CSS_SELECTOR,
                                        new Value(
                                            ValueTypes::STRING,
                                            '.imported-page-element-selector'
                                        )
                                    ),
                                    AssertionComparisons::EXISTS
                                )
                            ]
                        ),
                        'step referencing imported step with imported data provider' => (new Step(
                            [
                                new WaitAction('wait 10', '10'),
                            ],
                            [
                                new Assertion(
                                    '".imported-step-selector" exists',
                                    new Identifier(
                                        IdentifierTypes::CSS_SELECTOR,
                                        new Value(
                                            ValueTypes::STRING,
                                            '.imported-step-selector'
                                        )
                                    ),
                                    AssertionComparisons::EXISTS
                                )
                            ]
                        ))->withDataSetCollection(new DataSetCollection([
                            new DataSet([
                                'foo' => 'bar',
                            ])
                        ]))->withElementIdentifiers([
                            'element_name' => new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                new Value(
                                    ValueTypes::STRING,
                                    '.imported-page-element-selector'
                                ),
                                1,
                                'element_name'
                            ),
                        ]),
                    ]
                ),
            ],
        ];
    }
}
