<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Tests\Unit;

use webignition\BasilLoader\Exception\EmptyTestException;
use webignition\BasilLoader\SourceLoader;
use webignition\BasilLoader\Tests\Services\FixturePathFinder;
use webignition\BasilModels\TestSuite\TestSuite;
use webignition\BasilModels\TestSuite\TestSuiteInterface;
use webignition\BasilParser\Test\TestParser;

class SourceLoaderTest extends \PHPUnit\Framework\TestCase
{
    private SourceLoader $sourceLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sourceLoader = SourceLoader::createLoader();
    }

    /**
     * @dataProvider loadDataProvider
     */
    public function testLoad(string $path, TestSuiteInterface $expectedTestSuite)
    {
        $testSuite = $this->sourceLoader->load($path);

        $this->assertEquals($expectedTestSuite, $testSuite);
    }

    public function loadDataProvider(): array
    {
        $testParser = TestParser::create();

        return [
            'single non-empty test' => [
                'path' => FixturePathFinder::find('Test/example.com.verify-open-literal.yml'),
                'expectedTestSuite' => new TestSuite(
                    FixturePathFinder::find('Test/example.com.verify-open-literal.yml'),
                    [
                        $testParser->parse([
                            'config' => [
                                'browser' => 'chrome',
                                'url' => 'https://example.com',
                            ],
                            'verify page is open' => [
                                'assertions' => [
                                    '$page.url is "https://example.com"'
                                ],
                            ],
                        ])->withPath(FixturePathFinder::find('Test/example.com.verify-open-literal.yml')),
                    ]
                ),
            ],
            'single-test suite' => [
                'path' => FixturePathFinder::find('TestSuite/example.com-verify-open-literal.yml'),
                'expectedTestSuite' => new TestSuite(
                    FixturePathFinder::find('TestSuite/example.com-verify-open-literal.yml'),
                    [
                        $testParser->parse([
                            'config' => [
                                'browser' => 'chrome',
                                'url' => 'https://example.com',
                            ],
                            'verify page is open' => [
                                'assertions' => [
                                    '$page.url is "https://example.com"'
                                ],
                            ],
                        ])->withPath(FixturePathFinder::find('Test/example.com.verify-open-literal.yml')),
                    ]
                ),
            ],
            'multi-test suite' => [
                'path' => FixturePathFinder::find('TestSuite/example.com-all.yml'),
                'expectedTestSuite' => new TestSuite(
                    FixturePathFinder::find('TestSuite/example.com-all.yml'),
                    [
                        $testParser->parse(
                            [
                                'config' => [
                                    'browser' => 'chrome',
                                    'url' => 'https://example.com',
                                ],
                                'verify page is open' => [
                                    'assertions' => [
                                        '$page.url is "https://example.com"'
                                    ],
                                ],
                            ]
                        )->withPath(FixturePathFinder::find('Test/example.com.verify-open-literal.yml')),
                        $testParser->parse(
                            [
                                'config' => [
                                    'browser' => 'chrome',
                                    'url' => 'https://example.com',
                                ],
                                'verify page is open' => [
                                    'assertions' => [
                                        '$page.url is "https://example.com"'
                                    ],
                                ],
                            ]
                        )->withPath(FixturePathFinder::find('Test/example.com.import-step-verify-open-literal.yml')),
                    ]
                ),
            ],
            'verify open literal with multiple browsers' => [
                'path' => FixturePathFinder::find('Test/example.com.verify-open-literal-multiple-browsers.yml'),
                'expectedTestSuite' => new TestSuite(
                    FixturePathFinder::find('Test/example.com.verify-open-literal-multiple-browsers.yml'),
                    [
                        $testParser->parse(
                            [
                                'config' => [
                                    'browser' => 'chrome',
                                    'url' => 'https://example.com',
                                ],
                                'verify page is open' => [
                                    'assertions' => [
                                        '$page.url is "https://example.com"'
                                    ],
                                ],
                            ]
                        )->withPath(FixturePathFinder::find(
                            'Test/example.com.verify-open-literal-multiple-browsers.yml'
                        )),
                        $testParser->parse(
                            [
                                'config' => [
                                    'browser' => 'firefox',
                                    'url' => 'https://example.com',
                                ],
                                'verify page is open' => [
                                    'assertions' => [
                                        '$page.url is "https://example.com"'
                                    ],
                                ],
                            ]
                        )->withPath(FixturePathFinder::find(
                            'Test/example.com.verify-open-literal-multiple-browsers.yml'
                        )),
                    ]
                ),
            ],
        ];
    }

    public function testLoadEmptyFile()
    {
        $path = FixturePathFinder::find('Empty/empty.yml');
        $this->expectExceptionObject(new EmptyTestException($path));

        $this->sourceLoader->load($path);
    }
}
