<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Tests\Unit;

use webignition\BasilLoader\Exception\NonRetrievableDataProviderException;
use webignition\BasilLoader\Exception\NonRetrievablePageException;
use webignition\BasilLoader\Exception\NonRetrievableStepException;
use webignition\BasilLoader\TestLoader;
use webignition\BasilLoader\Tests\Services\FixturePathFinder;
use webignition\BasilModel\Action\ActionTypes;
use webignition\BasilModel\Action\InteractionAction;
use webignition\BasilModel\Assertion\AssertionComparison;
use webignition\BasilModel\Assertion\ComparisonAssertion;
use webignition\BasilModel\DataSet\DataSet;
use webignition\BasilModel\DataSet\DataSetCollection;
use webignition\BasilModel\Identifier\DomIdentifier;
use webignition\BasilModel\Step\Step;
use webignition\BasilModel\Test\Configuration;
use webignition\BasilModel\Test\Test;
use webignition\BasilModel\Test\TestInterface;
use webignition\BasilModel\Value\DomIdentifierValue;
use webignition\BasilModel\Value\LiteralValue;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ObjectValueType;
use webignition\BasilTestIdentifierFactory\TestIdentifierFactory;

class TestLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TestLoader
     */
    private $testLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testLoader = TestLoader::createLoader();
    }

    /**
     * @dataProvider loadDataProvider
     */
    public function testLoad(string $path, TestInterface $expectedTest)
    {
        $test = $this->testLoader->load($path);

        $this->assertEquals($expectedTest, $test);
    }

    public function loadDataProvider(): array
    {
        return [
            'empty' => [
                'path' => FixturePathFinder::find('Empty/empty.yml'),
                'expectedTest' => new Test(
                    FixturePathFinder::find('Empty/empty.yml'),
                    new Configuration('', ''),
                    []
                ),
            ],
            'non-empty' => [
                'path' => FixturePathFinder::find('Test/example.com.verify-open-literal.yml'),
                'expectedTest' => new Test(
                    FixturePathFinder::find('Test/example.com.verify-open-literal.yml'),
                    new Configuration('chrome', 'https://example.com'),
                    [
                        'verify page is open' => new Step(
                            [],
                            [
                                new ComparisonAssertion(
                                    '$page.url is "https://example.com"',
                                    new ObjectValue(ObjectValueType::PAGE_PROPERTY, '$page.url', 'url'),
                                    AssertionComparison::IS,
                                    new LiteralValue('https://example.com')
                                ),
                            ]
                        )
                    ]
                ),
            ],
            'import step verify open literal' => [
                'path' => FixturePathFinder::find('Test/example.com.import-step-verify-open-literal.yml'),
                'expectedTest' => new Test(
                    FixturePathFinder::find('Test/example.com.import-step-verify-open-literal.yml'),
                    new Configuration('chrome', 'https://example.com'),
                    [
                        'verify page is open' => new Step(
                            [],
                            [
                                new ComparisonAssertion(
                                    '$page.url is "https://example.com"',
                                    new ObjectValue(ObjectValueType::PAGE_PROPERTY, '$page.url', 'url'),
                                    AssertionComparison::IS,
                                    new LiteralValue('https://example.com')
                                ),
                            ]
                        )
                    ]
                ),
            ],
            'import step with data parameters' => [
                'path' => FixturePathFinder::find('Test/example.com.import-step-data-parameters.yml'),
                'expectedTest' => new Test(
                    FixturePathFinder::find('Test/example.com.import-step-data-parameters.yml'),
                    new Configuration('chrome', 'https://example.com'),
                    [
                        'data parameters step' => (new Step(
                            [
                                new InteractionAction(
                                    'click ".button"',
                                    ActionTypes::CLICK,
                                    new DomIdentifier('.button'),
                                    '".button"'
                                )
                            ],
                            [
                                new ComparisonAssertion(
                                    '".heading" includes $data.expected_title',
                                    DomIdentifierValue::create('.heading'),
                                    AssertionComparison::INCLUDES,
                                    new ObjectValue(
                                        ObjectValueType::DATA_PARAMETER,
                                        '$data.expected_title',
                                        'expected_title'
                                    )
                                ),
                            ]
                        ))->withDataSetCollection(new DataSetCollection([
                            new DataSet('0', [
                                'expected_title' => 'Foo',
                            ]),
                            new DataSet('1', [
                                'expected_title' => 'Bar',
                            ]),
                        ]))
                    ]
                ),
            ],
            'import step with element parameters and imported page' => [
                'path' => FixturePathFinder::find('Test/example.com.import-step-element-parameters.yml'),
                'expectedTest' => new Test(
                    FixturePathFinder::find('Test/example.com.import-step-element-parameters.yml'),
                    new Configuration('chrome', 'https://example.com'),
                    [
                        'element parameters step' => new Step(
                            [
                                new InteractionAction(
                                    'click $elements.button',
                                    ActionTypes::CLICK,
                                    TestIdentifierFactory::createElementIdentifier(
                                        '.button',
                                        null,
                                        'button'
                                    ),
                                    '$elements.button'
                                )
                            ],
                            [
                                new ComparisonAssertion(
                                    '$elements.heading includes "example"',
                                    new DomIdentifierValue(
                                        TestIdentifierFactory::createElementIdentifier(
                                            '.heading',
                                            null,
                                            'heading'
                                        )
                                    ),
                                    AssertionComparison::INCLUDES,
                                    new LiteralValue('example')
                                ),
                            ]
                        )
                    ]
                ),
            ],
        ];
    }

    public function testLoadThrowsNonRetrievableDataProvider()
    {
        $expectedInvalidPath = sprintf(
            '%s/DataProvider/file-does-not-exist.yml',
            str_replace('/Services/../', '/', FixturePathFinder::getBasePath())
        );

        $this->expectException(NonRetrievableDataProviderException::class);
        $this->expectExceptionMessage(sprintf(
            'Cannot retrieve data provider "data_provider_import_name" from "%s"',
            $expectedInvalidPath
        ));

        $this->testLoader->load(FixturePathFinder::find('Test/example.com.import-non-retrievable-data-provider.yml'));
    }


    public function testLoadThrowsNonRetrievablePageException()
    {
        $expectedInvalidPath = sprintf(
            '%s/Page/file-does-not-exist.yml',
            str_replace('/Services/../', '/', FixturePathFinder::getBasePath())
        );

        $this->expectException(NonRetrievablePageException::class);
        $this->expectExceptionMessage(sprintf(
            'Cannot retrieve page "page_import_name" from "%s"',
            $expectedInvalidPath
        ));

        $this->testLoader->load(FixturePathFinder::find('Test/example.com.import-non-retrievable-page-provider.yml'));
    }

    public function testLoadThrowsNonRetrievableStepException()
    {
        $expectedInvalidPath = sprintf(
            '%s/Step/file-does-not-exist.yml',
            str_replace('/Services/../', '/', FixturePathFinder::getBasePath())
        );

        $this->expectException(NonRetrievableStepException::class);
        $this->expectExceptionMessage(sprintf(
            'Cannot retrieve step "step_import_name" from "%s"',
            $expectedInvalidPath
        ));

        $this->testLoader->load(FixturePathFinder::find('Test/example.com.import-non-retrievable-step-provider.yml'));
    }
}
