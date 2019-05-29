<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Loader;

use webignition\BasilParser\Factory\StepFactory;
use webignition\BasilParser\Factory\Test\ConfigurationFactory;
use webignition\BasilParser\Factory\Test\TestFactory;
use webignition\BasilParser\Loader\TestLoader;
use webignition\BasilParser\Loader\YamlLoader;
use webignition\BasilParser\Model\Action\ActionTypes;
use webignition\BasilParser\Model\Action\InteractionAction;
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
use webignition\BasilParser\Tests\Services\TestFactoryFactory;

class TestLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider loadDataProvider
     */
    public function testLoad(string $path, array $yamlLoaderReturnValue, TestInterface $expectedTest)
    {
        $yamlLoader = \Mockery::mock(YamlLoader::class);
        $yamlLoader
            ->shouldReceive('loadArray')
            ->with($path)
            ->andReturn($yamlLoaderReturnValue);

        $testFactory = TestFactoryFactory::create();

        $testLoader = new TestLoader($yamlLoader, $testFactory);

        $test = $testLoader->load($path);

        $this->assertEquals($expectedTest, $test);
    }

    public function loadDataProvider(): array
    {
        return [
            'empty' => [
                'path' => 'empty-test.yml',
                'yamlLoaderReturnValue' => [],
                'expectedTest' => new Test(
                    'empty-test.yml',
                    new Configuration('', ''),
                    []
                ),
            ],
            'non-empty' => [
                'path' => 'non-empty-test.yml',
                'yamlLoaderReturnValue' => [
                    TestFactory::KEY_CONFIGURATION => [
                        ConfigurationFactory::KEY_BROWSER => 'chrome',
                        ConfigurationFactory::KEY_URL => 'http://example.com',
                    ],
                    'step name' => [
                        StepFactory::KEY_ACTIONS => [
                            'click ".selector"',
                        ],
                        StepFactory::KEY_ASSERTIONS => [
                            '$page.title is $data.expected_title',
                        ],
                    ],
                ],
                'expectedTest' => new Test(
                    'non-empty-test.yml',
                    new Configuration('chrome', 'http://example.com'),
                    [
                        'step name' => new Step(
                            [
                                new InteractionAction(
                                    ActionTypes::CLICK,
                                    new Identifier(
                                        IdentifierTypes::CSS_SELECTOR,
                                        '.selector'
                                    ),
                                    '".selector"'
                                ),
                            ],
                            [
                                new Assertion(
                                    '$page.title is $data.expected_title',
                                    new Identifier(
                                        IdentifierTypes::PAGE_OBJECT_PARAMETER,
                                        '$page.title'
                                    ),
                                    AssertionComparisons::IS,
                                    new Value(
                                        ValueTypes::DATA_PARAMETER,
                                        '$data.expected_title'
                                    )
                                ),
                            ]
                        )
                    ]
                ),
            ],
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        \Mockery::close();
    }
}
