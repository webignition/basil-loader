<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Tests\Unit;

use webignition\BasilLoader\Exception\UnknownTestException;
use webignition\BasilLoader\Exception\YamlLoaderException;
use webignition\BasilLoader\Tests\Services\FixturePathFinder;
use webignition\BasilLoader\TestSuiteLoader;
use webignition\BasilModels\Step\Step;
use webignition\BasilModels\Step\StepCollection;
use webignition\BasilModels\Test\Configuration;
use webignition\BasilModels\Test\Test;
use webignition\BasilModels\TestSuite\TestSuite;
use webignition\BasilModels\TestSuite\TestSuiteInterface;
use webignition\BasilParser\AssertionParser;
use webignition\PathResolver\PathResolver;

class TestSuiteLoaderTest extends \PHPUnit\Framework\TestCase
{
    private TestSuiteLoader $testSuiteLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testSuiteLoader = TestSuiteLoader::createLoader();
    }

    /**
     * @dataProvider loadDataProvider
     */
    public function testLoadSuccess(string $path, TestSuiteInterface $expectedTestSuite): void
    {
        $testSuite = $this->testSuiteLoader->load($path);

        $this->assertEquals($expectedTestSuite, $testSuite);
    }

    /**
     * @return array<mixed>
     */
    public function loadDataProvider(): array
    {
        $assertionParser = AssertionParser::create();

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
                        (new Test(
                            new Configuration('chrome', 'https://example.com'),
                            new StepCollection([
                                'verify page is open' => new Step(
                                    [],
                                    [
                                        $assertionParser->parse('$page.url is "https://example.com"'),
                                    ]
                                ),
                            ])
                        ))->withPath(FixturePathFinder::find('/Test/example.com.verify-open-literal.yml'))
                    ]
                ),
            ],
            'example all' => [
                'path' => FixturePathFinder::find('TestSuite/example.com-all.yml'),
                'expectedTestSuite' => new TestSuite(
                    FixturePathFinder::find('TestSuite/example.com-all.yml'),
                    [
                        (new Test(
                            new Configuration('chrome', 'https://example.com'),
                            new StepCollection([
                                'verify page is open' => new Step(
                                    [],
                                    [
                                        $assertionParser->parse('$page.url is "https://example.com"'),
                                    ]
                                ),
                            ])
                        ))->withPath(FixturePathFinder::find('/Test/example.com.verify-open-literal.yml')),
                        (new Test(
                            new Configuration('chrome', 'https://example.com'),
                            new StepCollection([
                                'verify page is open' => new Step(
                                    [],
                                    [
                                        $assertionParser->parse('$page.url is "https://example.com"'),
                                    ]
                                )
                            ])
                        ))->withPath(FixturePathFinder::find('Test/example.com.import-step-verify-open-literal.yml')),
                    ]
                ),
            ],
            'example verify open literal with multiple browsers' => [
                'path' => FixturePathFinder::find('TestSuite/example.com-verify-open-literal-multiple-browsers.yml'),
                'expectedTestSuite' => new TestSuite(
                    FixturePathFinder::find('TestSuite/example.com-verify-open-literal-multiple-browsers.yml'),
                    [
                        (new Test(
                            new Configuration('chrome', 'https://example.com'),
                            new StepCollection([
                                'verify page is open' => new Step(
                                    [],
                                    [
                                        $assertionParser->parse('$page.url is "https://example.com"'),
                                    ]
                                ),
                            ])
                        ))->withPath(FixturePathFinder::find(
                            'Test/example.com.verify-open-literal-multiple-browsers.yml'
                        )),
                        (new Test(
                            new Configuration('firefox', 'https://example.com'),
                            new StepCollection([
                                'verify page is open' => new Step(
                                    [],
                                    [
                                        $assertionParser->parse('$page.url is "https://example.com"'),
                                    ]
                                ),
                            ])
                        ))->withPath(FixturePathFinder::find(
                            'Test/example.com.verify-open-literal-multiple-browsers.yml'
                        )),
                    ]
                ),
            ],
        ];
    }

    public function testLoadTestImportPathDoesNotExist(): void
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

    public function testLoadFromTestPathsListReThrowsYamlLoaderException(): void
    {
        $path = FixturePathFinder::find('TestSuite/imports-invalid.yml');
        $basePath = FixturePathFinder::find('TestSuite');

        $this->expectException(YamlLoaderException::class);

        $this->testSuiteLoader->loadFromTestPathList($path, $basePath, [
            '../invalid-yaml.yml',
        ]);
    }
}
