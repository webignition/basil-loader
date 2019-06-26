<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\DataStructure;

use webignition\BasilParser\DataStructure\Step;

class StepTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getActionStringsDataProvider
     */
    public function testGetActionStrings(Step $stepDataStructure, array $expectedActionStrings)
    {
        $this->assertSame($expectedActionStrings, $stepDataStructure->getActionStrings());
    }

    public function getActionStringsDataProvider(): array
    {
        return [
            'not present' => [
                'stepDataStructure' => new Step([]),
                'expectedActionStrings' => [],
            ],
            'not an array' => [
                'stepDataStructure' => new Step([
                    Step::KEY_ACTIONS => 'actions',
                ]),
                'expectedActionStrings' => [],
            ],
            'empty' => [
                'stepDataStructure' => new Step([
                    Step::KEY_ACTIONS => [],
                ]),
                'expectedActionStrings' => [],
            ],
            'non-empty' => [
                'stepDataStructure' => new Step([
                    Step::KEY_ACTIONS => [
                        'click ".selector"',
                        'set ".input" to "value"',
                    ],
                ]),
                'expectedActionStrings' => [
                    'click ".selector"',
                    'set ".input" to "value"',
                ],
            ],
        ];
    }

    /**
     * @dataProvider getAssertionStringsDataProvider
     */
    public function testGetAssertionStrings(Step $stepDataStructure, array $expectedAssertionStrings)
    {
        $this->assertSame($expectedAssertionStrings, $stepDataStructure->getAssertionStrings());
    }

    public function getAssertionStringsDataProvider(): array
    {
        return [
            'not present' => [
                'stepDataStructure' => new Step([]),
                'expectedAssertionStrings' => [],
            ],
            'not an array' => [
                'stepDataStructure' => new Step([
                    Step::KEY_ASSERTIONS => 'actions',
                ]),
                'expectedAssertionStrings' => [],
            ],
            'empty' => [
                'stepDataStructure' => new Step([
                    Step::KEY_ASSERTIONS => [],
                ]),
                'expectedAssertionStrings' => [],
            ],
            'non-empty' => [
                'stepDataStructure' => new Step([
                    Step::KEY_ASSERTIONS => [
                        '".selector" exists',
                    ],
                ]),
                'expectedAssertionStrings' => [
                    '".selector" exists',
                ],
            ],
        ];
    }
}
