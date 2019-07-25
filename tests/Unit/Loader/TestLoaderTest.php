<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Loader;

use webignition\BasilModel\Assertion\Assertion;
use webignition\BasilModel\Assertion\AssertionComparisons;
use webignition\BasilModel\Step\Step;
use webignition\BasilModel\Test\Configuration;
use webignition\BasilModel\Test\Test;
use webignition\BasilModel\Test\TestInterface;
use webignition\BasilModel\Value\LiteralValue;
use webignition\BasilModel\Value\ObjectNames;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilParser\Loader\TestLoader;
use webignition\BasilParser\Tests\Services\FixturePathFinder;

class TestLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider loadDataProvider
     */
    public function testLoad(string $path, TestInterface $expectedTest)
    {
        $testLoader = TestLoader::createLoader();

        $test = $testLoader->load($path);

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
                                new Assertion(
                                    '$page.url is "https://example.com"',
                                    new ObjectValue(
                                        ValueTypes::PAGE_OBJECT_PROPERTY,
                                        '$page.url',
                                        ObjectNames::PAGE,
                                        'url'
                                    ),
                                    AssertionComparisons::IS,
                                    LiteralValue::createStringValue('https://example.com')
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
                                new Assertion(
                                    '$page.url is "https://example.com"',
                                    new ObjectValue(
                                        ValueTypes::PAGE_OBJECT_PROPERTY,
                                        '$page.url',
                                        'page',
                                        'url'
                                    ),
                                    AssertionComparisons::IS,
                                    LiteralValue::createStringValue('https://example.com')
                                ),
                            ]
                        )
                    ]
                ),
            ],
        ];
    }
}
