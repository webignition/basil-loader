<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Factory\Test;

use webignition\BasilParser\Factory\StepFactory;
use webignition\BasilParser\Factory\Test\ConfigurationFactory;
use webignition\BasilParser\Factory\Test\TestFactory;
use webignition\BasilParser\Model\Action\ActionTypes;
use webignition\BasilParser\Model\Action\InputAction;
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

class TestFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TestFactory
     */
    private $testFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testFactory = new TestFactory();
    }

    /**
     * @dataProvider createFromTestDataDataProvider
     */
    public function testCreateFromTestData(array $testData, TestInterface $expectedTest)
    {
        $test = $this->testFactory->createFromTestData($testData);

        $this->assertEquals($expectedTest, $test);
    }

    public function createFromTestDataDataProvider(): array
    {
        $configurationData = [
            ConfigurationFactory::KEY_BROWSER => 'chrome',
            ConfigurationFactory::KEY_URL => 'http://example.com',
        ];

        $expectedConfiguration = new Configuration('chrome', 'http://example.com');

        return [
            'empty' => [
                'testData' => [],
                'expectedTest' => new Test(
                    new Configuration('', ''),
                    []
                ),
            ],
            'configuration only' => [
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => $configurationData,
                ],
                'expectedTest' => new Test($expectedConfiguration, []),
            ],
            'invalid inline steps only' => [
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => $configurationData,
                    'invalid' => [
                        StepFactory::KEY_ACTIONS => true,
                        StepFactory::KEY_ASSERTIONS => [
                            '',
                            false,
                        ],
                    ],
                ],
                'expectedTest' => new Test($expectedConfiguration, [
                    'invalid' => new Step([], []),
                ]),
            ],
            'inline steps only' => [
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => $configurationData,
                    'verify page is open' => [
                        StepFactory::KEY_ASSERTIONS => [
                            '$page.url is "http://example.com"',
                        ],
                    ],
                    'query "example"' => [
                        StepFactory::KEY_ACTIONS => [
                            'click ".form .submit"',
                        ],
                        StepFactory::KEY_ASSERTIONS => [
                            '$page.title is "example - Example Domain"',
                        ],
                    ],
                ],
                'expectedTest' => new Test($expectedConfiguration, [
                    'verify page is open' => new Step([], [
                        new Assertion(
                            '$page.url is "http://example.com"',
                            new Identifier(
                                IdentifierTypes::PAGE_OBJECT_PARAMETER,
                                '$page.url'
                            ),
                            AssertionComparisons::IS,
                            new Value(
                                ValueTypes::STRING,
                                'http://example.com'
                            )
                        ),
                    ]),
                    'query "example"' => new Step(
                        [
                            new InteractionAction(
                                ActionTypes::CLICK,
                                new Identifier(
                                    IdentifierTypes::CSS_SELECTOR,
                                    '.form .submit'
                                ),
                                '".form .submit"'
                            ),
                        ],
                        [
                            new Assertion(
                                '$page.title is "example - Example Domain"',
                                new Identifier(
                                    IdentifierTypes::PAGE_OBJECT_PARAMETER,
                                    '$page.title'
                                ),
                                AssertionComparisons::IS,
                                new Value(
                                    ValueTypes::STRING,
                                    'example - Example Domain'
                                )
                            ),
                        ]
                    ),
                ]),
            ],
        ];
    }
}
