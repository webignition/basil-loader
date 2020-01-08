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
    /**
     * @var SourceLoader
     */
    private $sourceLoader;

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
                        $testParser->parse('', FixturePathFinder::find('Test/example.com.verify-open-literal.yml'), [
                            'config' => [
                                'browser' => 'chrome',
                                'url' => 'https://example.com',
                            ],
                            'verify page is open' => [
                                'assertions' => [
                                    '$page.url is "https://example.com"'
                                ],
                            ],
                        ]),
                    ]
                ),
            ],
            'single-test suite' => [
                'path' => FixturePathFinder::find('TestSuite/example.com-verify-open-literal.yml'),
                'expectedTestSuite' => new TestSuite(
                    FixturePathFinder::find('TestSuite/example.com-verify-open-literal.yml'),
                    [
                        $testParser->parse('', FixturePathFinder::find('Test/example.com.verify-open-literal.yml'), [
                            'config' => [
                                'browser' => 'chrome',
                                'url' => 'https://example.com',
                            ],
                            'verify page is open' => [
                                'assertions' => [
                                    '$page.url is "https://example.com"'
                                ],
                            ],
                        ]),
                    ]
                ),
            ],
            'multi-test suite' => [
                'path' => FixturePathFinder::find('TestSuite/example.com-all.yml'),
                'expectedTestSuite' => new TestSuite(
                    FixturePathFinder::find('TestSuite/example.com-all.yml'),
                    [
                        $testParser->parse(
                            '',
                            FixturePathFinder::find('Test/example.com.verify-open-literal.yml'),
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
                        ),
                        $testParser->parse(
                            '',
                            FixturePathFinder::find('Test/example.com.import-step-verify-open-literal.yml'),
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
                        ),
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
