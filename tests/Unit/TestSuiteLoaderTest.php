<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Tests\Unit;

use webignition\BasilLoader\Exception\UnknownTestException;
use webignition\BasilLoader\Tests\Services\FixturePathFinder;
use webignition\BasilLoader\TestSuiteLoader;
use webignition\BasilModels\Assertion\ComparisonAssertion;
use webignition\BasilModels\Step\Step;
use webignition\BasilModels\Test\Configuration;
use webignition\BasilModels\Test\Test;
use webignition\BasilModels\TestSuite\TestSuite;
use webignition\BasilModels\TestSuite\TestSuiteInterface;
use webignition\PathResolver\PathResolver;

class TestSuiteLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TestSuiteLoader
     */
    private $testSuiteLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testSuiteLoader = TestSuiteLoader::createLoader();
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
                                        new ComparisonAssertion(
                                            '$page.url is "https://example.com"',
                                            '$page.url',
                                            'is',
                                            '"https://example.com"'
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
                                        new ComparisonAssertion(
                                            '$page.url is "https://example.com"',
                                            '$page.url',
                                            'is',
                                            '"https://example.com"'
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
                                        new ComparisonAssertion(
                                            '$page.url is "https://example.com"',
                                            '$page.url',
                                            'is',
                                            '"https://example.com"'
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
        $pathResolver = new PathResolver();

        $expectedUnknownTestPath = $pathResolver->resolve(
            __DIR__,
            '../Fixtures/Test/example.com.path-does-not-exist.yml'
        );

        $path = FixturePathFinder::find('TestSuite/example.com-path-does-not-exist.yml');

        $this->expectException(UnknownTestException::class);
        $this->expectExceptionMessage('Unknown test "' . $expectedUnknownTestPath . '"');

        $this->testSuiteLoader->load($path);
    }
}
