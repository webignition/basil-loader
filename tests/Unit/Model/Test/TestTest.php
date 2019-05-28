<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Model\Test;

use webignition\BasilParser\Model\Step\Step;
use webignition\BasilParser\Model\Test\Configuration;
use webignition\BasilParser\Model\Test\Test;
use webignition\BasilParser\Model\Test\TestInterface;

class TestTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(string $name, Configuration $configuration, array $steps, TestInterface $expectedTest)
    {
        $test = new Test($name, $configuration, $steps);

        $this->assertEquals($expectedTest, $test);
    }

    public function createDataProvider()
    {
        return [
            'no steps' => [
                'name' => 'no steps',
                'configuration' => new Configuration('chrome', 'http://example.com'),
                'steps' => [],
                'expectedTest' => new Test(
                    'no steps',
                    new Configuration('chrome', 'http://example.com'),
                    []
                ),
            ],
            'invalid steps' => [
                'name' => 'invalid steps',
                'configuration' => new Configuration('chrome', 'http://example.com'),
                'steps' => [
                    1,
                    'foo',
                ],
                'expectedTest' => new Test(
                    'invalid steps',
                    new Configuration('chrome', 'http://example.com'),
                    []
                ),
            ],
            'has steps' => [
                'name' => 'has steps',
                'configuration' => new Configuration('chrome', 'http://example.com'),
                'steps' => [
                    new Step([], []),
                ],
                'expectedTest' => new Test(
                    'has steps',
                    new Configuration('chrome', 'http://example.com'),
                    [
                        new Step([], []),
                    ]
                ),
            ],
        ];
    }
}
