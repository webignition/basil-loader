<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Model\TestSuite;

use webignition\BasilParser\Model\Step\Step;
use webignition\BasilParser\Model\Test\Configuration;
use webignition\BasilParser\Model\Test\Test;
use webignition\BasilParser\Model\Test\TestInterface;
use webignition\BasilParser\Model\TestSuite\TestSuite;

class TestSuiteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(array $tests, array $expectedTests)
    {
        $testSuite = new TestSuite($tests);

        $this->assertSame($expectedTests, $testSuite->getTests());
    }

    public function createDataProvider()
    {
        $testOne = new Test(
            'test one',
            new Configuration('chrome', 'http://example.com/one'),
            []
        );

        $testTwo = new Test(
            'test two',
            new Configuration('chrome', 'http://example.com/two'),
            []
        );

        return [
            'no tests' => [
                'tests' => [],
                'expectedTests' => [],
            ],
            'non-test tests' => [
                'tests' => [
                    1,
                    true,
                    'string',
                ],
                'expectedTests' => [],
            ],
            'has tests' => [
                'tests' => [
                    $testOne,
                    $testTwo,
                ],
                'expectedTests' => [
                    $testOne,
                    $testTwo,
                ],
            ],
        ];
    }
}
