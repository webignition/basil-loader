<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Model\Test;

use webignition\BasilParser\Model\Step\Step;
use webignition\BasilParser\Model\Test\Configuration;
use webignition\BasilParser\Model\Test\Test;
use webignition\BasilParser\Model\Test\TestInterface;

class TestTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(Configuration $configuration, array $steps, TestInterface $expectedTest)
    {
        $test = new Test($configuration, $steps);

        $this->assertEquals($expectedTest, $test);
    }

    public function createDataProvider()
    {
        return [
            'no steps' => [
                'configuration' => new Configuration('chrome', 'http://example.com'),
                'steps' => [],
                'expectedTest' => new Test(
                    new Configuration('chrome', 'http://example.com'),
                    []
                ),
            ],
            'invalid steps' => [
                'configuration' => new Configuration('chrome', 'http://example.com'),
                'steps' => [
                    1,
                    'foo',
                ],
                'expectedTest' => new Test(
                    new Configuration('chrome', 'http://example.com'),
                    []
                ),
            ],
            'has steps' => [
                'configuration' => new Configuration('chrome', 'http://example.com'),
                'steps' => [
                    new Step([], []),
                ],
                'expectedTest' => new Test(
                    new Configuration('chrome', 'http://example.com'),
                    [
                        new Step([], []),
                    ]
                ),
            ],
        ];
    }
}
