<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Builder;

use Symfony\Component\Yaml\Parser as YamlParser;
use webignition\BasilParser\Builder\InvalidPageElementReferenceException;
use webignition\BasilParser\Builder\StepBuilder;
use webignition\BasilParser\Builder\UnknownDataProviderImportException;
use webignition\BasilParser\Builder\UnknownPageElementException;
use webignition\BasilParser\Builder\UnknownPageImportException;
use webignition\BasilParser\Builder\UnknownStepImportException;
use webignition\BasilParser\Factory\PageFactory;
use webignition\BasilParser\Factory\StepFactory;
use webignition\BasilParser\Loader\PageLoader;
use webignition\BasilParser\Loader\StepLoader;
use webignition\BasilParser\Loader\YamlLoader;
use webignition\BasilParser\Model\Action\ActionTypes;
use webignition\BasilParser\Model\Action\InputAction;
use webignition\BasilParser\Model\Action\InteractionAction;
use webignition\BasilParser\Model\Assertion\Assertion;
use webignition\BasilParser\Model\Assertion\AssertionComparisons;
use webignition\BasilParser\Model\Identifier\Identifier;
use webignition\BasilParser\Model\Identifier\IdentifierTypes;
use webignition\BasilParser\Model\Step\Step;
use webignition\BasilParser\Model\Step\StepInterface;
use webignition\BasilParser\Model\Value\Value;
use webignition\BasilParser\Model\Value\ValueTypes;
use webignition\BasilParser\Tests\Services\FixturePathFinder;

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
        $pageFactory = new PageFactory();

        $yamlParser = new YamlParser();
        $yamlLoader = new YamlLoader($yamlParser);
        $pageLoader = new PageLoader($yamlLoader, $pageFactory);
        $stepLoader = new StepLoader($yamlLoader, $stepFactory);

        $this->stepBuilder = new StepBuilder($stepFactory, $pageLoader, $stepLoader, $yamlLoader);
    }

    /**
     * @dataProvider buildSuccessDataProvider
     */
    public function testBuildSuccess(
        array $stepData,
        array $stepImportPaths,
        array $dataProviderImportPaths,
        array $pages,
        StepInterface $expectedStep
    ) {
        $step = $this->stepBuilder->build(
            'Step Name',
            $stepData,
            $stepImportPaths,
            $dataProviderImportPaths,
            $pages
        );

        $this->assertInstanceOf(StepInterface::class, $step);
        $this->assertEquals($expectedStep, $step);
    }

    public function buildSuccessDataProvider(): array
    {
        return [
//            'no imports, no actions, no assertions' => [
//                'stepData' => [],
//                'stepImportPaths' => [],
//                'dataProviderImportPaths' => [],
//                'pages' => [],
//                'expectedStep' => new Step([], []),
//            ],
//            'no imports, empty actions, empty assertions' => [
//                'stepData' => [
//                    StepFactory::KEY_ACTIONS => [],
//                    StepFactory::KEY_ASSERTIONS => [],
//                ],
//                'stepImportPaths' => [],
//                'dataProviderImportPaths' => [],
//                'pages' => [],
//                'expectedStep' => new Step([], []),
//            ],
//            'unused invalid imports, empty actions, empty assertions' => [
//                'stepData' => [
//                    StepFactory::KEY_ACTIONS => [],
//                    StepFactory::KEY_ASSERTIONS => [],
//                ],
//                'stepImportPaths' => [
//                    'invalid' => 'invalid.yml',
//                ],
//                'dataProviderImportPaths' => [
//                    'invalid' => 'invalid.yml',
//                ],
//                'pages' => [],
//                'expectedStep' => new Step([], []),
//            ],
//            'no imports, has actions, has assertions' => [
//                'stepData' => [
//                    StepFactory::KEY_ACTIONS => [
//                        'click ".selector"',
//                    ],
//                    StepFactory::KEY_ASSERTIONS => [
//                        '$page.title is "Example"',
//                    ],
//                ],
//                'stepImportPaths' => [],
//                'dataProviderImportPaths' => [],
//                'pages' => [],
//                'expectedStep' => new Step(
//                    [
//                        new InteractionAction(
//                            ActionTypes::CLICK,
//                            new Identifier(
//                                IdentifierTypes::CSS_SELECTOR,
//                                '.selector'
//                            ),
//                            '".selector"'
//                        )
//                    ],
//                    [
//                        new Assertion(
//                            '$page.title is "Example"',
//                            new Identifier(
//                                IdentifierTypes::PAGE_OBJECT_PARAMETER,
//                                '$page.title'
//                            ),
//                            AssertionComparisons::IS,
//                            new Value(
//                                ValueTypes::STRING,
//                                'Example'
//                            )
//                        )
//                    ]
//                ),
//            ],
            'no imports, inline step with page model element references' => [
                'stepData' => [
                    StepFactory::KEY_ACTIONS => [
                        'set page_import_name.elements.element_name to "example"',
                    ],
                    StepFactory::KEY_ASSERTIONS => [
                        'page_import_name.elements.element_name is "example"',
                    ],
                ],
                'stepImportPaths' => [],
                'dataProviderImportPaths' => [],
                'pages' => [],
                'expectedStep' => new Step(
                    [
                        new InputAction(
                            new Identifier(
                                IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                                'page_import_name.elements.element_name'
                            ),
                            new Value(
                                ValueTypes::STRING,
                                'example'
                            ),
                            'page_import_name.elements.element_name to "example"'
                        ),
                    ],
                    [
                        new Assertion(
                            'page_import_name.elements.element_name is "example"',
                            new Identifier(
                                IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                                'page_import_name.elements.element_name'
                            ),
                            AssertionComparisons::IS,
                            new Value(
                                ValueTypes::STRING,
                                'example'
                            )
                        )
                    ]
                ),
            ],
            'import step' => [
                'stepData' => [
                    StepBuilder::KEY_USE => 'step_import_name',
                ],
                'stepImportPaths' => [
                    'step_import_name' => FixturePathFinder::find('Step/no-parameters.yml'),
                ],
                'dataProviderImportPaths' => [],
                'pages' => [],
                'expectedStep' => new Step(
                    [
                        new InteractionAction(
                            ActionTypes::CLICK,
                            new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                '.button'
                            ),
                            '".button"'
                        )
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
            'inline data' => [
                'stepData' => [
                    StepBuilder::KEY_USE => 'step_import_name',
                    StepBuilder::KEY_DATA => [
                        [
                            'expected_title' => 'Foo',
                        ],
                        [
                            'expected_title' => 'Bar',
                        ],
                    ],
                ],
                'stepImportPaths' => [
                    'step_import_name' => FixturePathFinder::find('Step/data-parameters.yml'),
                ],
                'dataProviderImportPaths' => [],
                'pages' => [],
                'expectedStep' => new Step(
                    [
                        new InteractionAction(
                            ActionTypes::CLICK,
                            new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                '.button'
                            ),
                            '".button"'
                        )
                    ],
                    [
                        new Assertion(
                            '".heading" includes $data.expected_title',
                            new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                '.heading'
                            ),
                            AssertionComparisons::INCLUDES,
                            new Value(
                                ValueTypes::DATA_PARAMETER,
                                '$data.expected_title'
                            )
                        ),
                    ]
                ),
            ],
            'imported data' => [
                'stepData' => [
                    StepBuilder::KEY_USE => 'step_import_name',
                    StepBuilder::KEY_DATA => 'data_provider_name',
                ],
                'stepImportPaths' => [
                    'step_import_name' => FixturePathFinder::find('Step/data-parameters.yml'),
                ],
                'dataProviderImportPaths' => [
                    'data_provider_name' => FixturePathFinder::find('DataProvider/expected-title-only.yml'),
                ],
                'pages' => [],
                'expectedStep' => new Step(
                    [
                        new InteractionAction(
                            ActionTypes::CLICK,
                            new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                '.button'
                            ),
                            '".button"'
                        )
                    ],
                    [
                        new Assertion(
                            '".heading" includes $data.expected_title',
                            new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                '.heading'
                            ),
                            AssertionComparisons::INCLUDES,
                            new Value(
                                ValueTypes::DATA_PARAMETER,
                                '$data.expected_title'
                            )
                        ),
                    ]
                ),
            ],
            'element parameters' => [
                'stepData' => [
                    StepBuilder::KEY_USE => 'step_import_name',
                    StepBuilder::KEY_ELEMENTS => [
                        'heading' => 'page_import_name.elements.heading',
                    ],
                ],
                'stepImportPaths' => [
                    'step_import_name' => FixturePathFinder::find('Step/element-parameters.yml'),
                ],
                'dataProviderImportPaths' => [],
                'pagesImportPaths' => [
                    'page_import_name' => FixturePathFinder::find('Page/example.com.heading.yml'),
                ],
                'expectedStep' =>
                    (new Step(
                        [
                            new InteractionAction(
                                ActionTypes::CLICK,
                                new Identifier(
                                    IdentifierTypes::CSS_SELECTOR,
                                    '.button'
                                ),
                                '".button"'
                            )
                        ],
                        [
                            new Assertion(
                                '$elements.heading includes "Hello World"',
                                new Identifier(
                                    IdentifierTypes::ELEMENT_PARAMETER,
                                    '$elements.heading'
                                ),
                                AssertionComparisons::INCLUDES,
                                new Value(
                                    ValueTypes::STRING,
                                    'Hello World'
                                )
                            ),
                        ]
                    ))->withElementIdentifiers([
                        'heading' => new Identifier(
                            IdentifierTypes::CSS_SELECTOR,
                            '.heading',
                            null,
                            'heading'
                        ),
                    ]),
            ],
        ];
    }

    public function testBuildUseUnknownStepImport()
    {
        $this->expectException(UnknownStepImportException::class);
        $this->expectExceptionMessage('Unknown step import "unknown_step_import_name" in step "Step Name"');

        $this->stepBuilder->build(
            'Step Name',
            [
                StepBuilder::KEY_USE => 'unknown_step_import_name',
            ],
            [],
            [],
            []
        );
    }

    public function testBuildUseUnknownDataProviderImport()
    {
        $this->expectException(UnknownDataProviderImportException::class);
        $this->expectExceptionMessage('Unknown data provider import "unknown_data_provider_name" in step "Step Name"');

        $this->stepBuilder->build(
            'Step Name',
            [
                StepBuilder::KEY_USE => 'step_import_name',
                StepBuilder::KEY_DATA => 'unknown_data_provider_name',
            ],
            [
                'step_import_name' => FixturePathFinder::find('Step/data-parameters.yml'),
            ],
            [],
            []
        );
    }

    public function testBuildUseUnknownPageImport()
    {
        $this->expectException(UnknownPageImportException::class);
        $this->expectExceptionMessage('Unknown page import "page_import_name" in step "Step Name"');

        $this->stepBuilder->build(
            'Step Name',
            [
                StepBuilder::KEY_USE => 'step_import_name',
                StepBuilder::KEY_ELEMENTS => [
                    'heading' => 'page_import_name.elements.heading',
                ],
            ],
            [
                'step_import_name' => FixturePathFinder::find('Step/element-parameters.yml'),
            ],
            [],
            []
        );
    }

    public function testBuildUseUnknownPageElement()
    {
        $this->expectException(UnknownPageElementException::class);
        $this->expectExceptionMessage(
            'Unknown page element "not-heading" in page "page_import_name" in step "Step Name"'
        );

        $this->stepBuilder->build(
            'Step Name',
            [
                StepBuilder::KEY_USE => 'step_import_name',
                StepBuilder::KEY_ELEMENTS => [
                    'not-heading' => 'page_import_name.elements.not-heading',
                ],
            ],
            [
                'step_import_name' => FixturePathFinder::find('Step/element-parameters.yml'),
            ],
            [],
            [
                'page_import_name' => FixturePathFinder::find('Page/example.com.heading.yml'),
            ]
        );
    }

    public function testBuildUseInvalidPageElementReference()
    {
        $this->expectException(InvalidPageElementReferenceException::class);
        $this->expectExceptionMessage(
            'Invalid page element reference "page_import_name.foo.heading" in step "Step Name"'
        );

        $this->stepBuilder->build(
            'Step Name',
            [
                StepBuilder::KEY_USE => 'step_import_name',
                StepBuilder::KEY_ELEMENTS => [
                    'heading' => 'page_import_name.foo.heading',
                ],
            ],
            [
                'step_import_name' => FixturePathFinder::find('Step/element-parameters.yml'),
            ],
            [],
            [
                'page_import_name' => FixturePathFinder::find('Page/example.com.heading.yml'),
            ]
        );
    }
}
