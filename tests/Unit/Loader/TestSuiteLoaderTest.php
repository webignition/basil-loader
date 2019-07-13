<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Loader;

use webignition\BasilDataStructure\PathResolver;
use webignition\BasilModel\Assertion\Assertion;
use webignition\BasilModel\Assertion\AssertionComparisons;
use webignition\BasilModel\Identifier\Identifier;
use webignition\BasilModel\Identifier\IdentifierTypes;
use webignition\BasilModel\Step\Step;
use webignition\BasilModel\Test\Configuration;
use webignition\BasilModel\Test\Test;
use webignition\BasilModel\TestSuite\TestSuite;
use webignition\BasilModel\TestSuite\TestSuiteInterface;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\Value;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilParser\Exception\UnknownTestException;
use webignition\BasilParser\Loader\TestSuiteLoader;
use webignition\BasilParser\Tests\Services\FixturePathFinder;
use webignition\BasilParser\Tests\Services\TestSuiteLoaderFactory;

class TestSuiteLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TestSuiteLoader
     */
    private $testSuiteLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testSuiteLoader = TestSuiteLoaderFactory::create();
    }

    /**
     * @dataProvider loadDataProvider
     */
    public function testLoadSuccess(string $path, TestSuiteInterface $expectedTestSuite)
    {
        $testSuite = $this->testSuiteLoader->load($path);

        $this->assertEquals($expectedTestSuite, $testSuite);
    }

    public function loadDataProvider(): array
    {
        return [
            'empty' => [
                'path' => FixturePathFinder::find('Empty/empty.yml'),
                'expectedTestSuite' => new TestSuite(FixturePathFinder::find('Empty/empty.yml'), []),
            ],
            'example verify open literal' => [
                'path' => FixturePathFinder::find('TestSuite/example.com-verify-open-literal.yml'),
                'expectedTestSuite' => new TestSuite(
                    FixturePathFinder::find('TestSuite/example.com-verify-open-literal.yml'),
                    [
                        new Test(
                            FixturePathFinder::find('/Test/example.com.verify-open-literal.yml'),
                            new Configuration('chrome', 'https://example.com'),
                            [
                                'verify page is open' => new Step(
                                    [],
                                    [
                                        new Assertion(
                                            '$page.url is "https://example.com"',
                                            new Identifier(
                                                IdentifierTypes::PAGE_OBJECT_PARAMETER,
                                                new ObjectValue(
                                                    ValueTypes::PAGE_OBJECT_PROPERTY,
                                                    '$page.url',
                                                    'page',
                                                    'url'
                                                )
                                            ),
                                            AssertionComparisons::IS,
                                            new Value(
                                                ValueTypes::STRING,
                                                'https://example.com'
                                            )
                                        ),
                                    ]
                                ),
                            ]
                        )
                    ]
                ),
            ],
            'example all' => [
                'path' => FixturePathFinder::find('TestSuite/example.com-all.yml'),
                'expectedTestSuite' => new TestSuite(
                    FixturePathFinder::find('TestSuite/example.com-all.yml'),
                    [
                        new Test(
                            FixturePathFinder::find('/Test/example.com.verify-open-literal.yml'),
                            new Configuration('chrome', 'https://example.com'),
                            [
                                'verify page is open' => new Step(
                                    [],
                                    [
                                        new Assertion(
                                            '$page.url is "https://example.com"',
                                            new Identifier(
                                                IdentifierTypes::PAGE_OBJECT_PARAMETER,
                                                new ObjectValue(
                                                    ValueTypes::PAGE_OBJECT_PROPERTY,
                                                    '$page.url',
                                                    'page',
                                                    'url'
                                                )
                                            ),
                                            AssertionComparisons::IS,
                                            new Value(
                                                ValueTypes::STRING,
                                                'https://example.com'
                                            )
                                        ),
                                    ]
                                ),
                            ]
                        ),
                        new Test(
                            FixturePathFinder::find('Test/example.com.import-step-verify-open-literal.yml'),
                            new Configuration('chrome', 'https://example.com'),
                            [
                                'verify page is open' => new Step(
                                    [],
                                    [
                                        new Assertion(
                                            '$page.url is "https://example.com"',
                                            new Identifier(
                                                IdentifierTypes::PAGE_OBJECT_PARAMETER,
                                                new ObjectValue(
                                                    ValueTypes::PAGE_OBJECT_PROPERTY,
                                                    '$page.url',
                                                    'page',
                                                    'url'
                                                )
                                            ),
                                            AssertionComparisons::IS,
                                            new Value(
                                                ValueTypes::STRING,
                                                'https://example.com'
                                            )
                                        ),
                                    ]
                                )
                            ]
                        ),
                    ]
                ),
            ],
        ];
    }

    public function testLoadTestImportPathDoesNotExist()
    {
        $expectedUnknownTestPath = (PathResolver::create())->resolve(
            __DIR__,
            '../../../Fixtures/Test/example.com.path-does-not-exist.yml'
        );

        $path = FixturePathFinder::find('TestSuite/example.com-path-does-not-exist.yml');

        $this->expectException(UnknownTestException::class);
        $this->expectExceptionMessage('Unknown test "' . $expectedUnknownTestPath . '"');

        $this->testSuiteLoader->load($path);
    }
}
