<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Loader;

use webignition\BasilParser\Model\Assertion\Assertion;
use webignition\BasilParser\Model\Assertion\AssertionComparisons;
use webignition\BasilParser\Model\Identifier\Identifier;
use webignition\BasilParser\Model\Identifier\IdentifierTypes;
use webignition\BasilParser\Model\Step\Step;
use webignition\BasilParser\Model\Test\Configuration;
use webignition\BasilParser\Model\Test\Test;
use webignition\BasilParser\Model\Test\TestInterface;
use webignition\BasilParser\Model\Value\Value;
use webignition\BasilParser\Model\Value\ValueTypes;
use webignition\BasilParser\Tests\Services\FixturePathFinder;
use webignition\BasilParser\Tests\Services\TestLoaderFactory;

class TestLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider loadDataProvider
     */
    public function testLoad(string $path, TestInterface $expectedTest)
    {
        $testLoader = TestLoaderFactory::create();

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
                                    new Identifier(
                                        IdentifierTypes::PAGE_OBJECT_PARAMETER,
                                        '$page.url'
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
                                    new Identifier(
                                        IdentifierTypes::PAGE_OBJECT_PARAMETER,
                                        '$page.url'
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
            ],
        ];
    }
}
