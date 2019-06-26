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

    /**
     * @dataProvider getImportNameDataProvider
     */
    public function testGetImportName(Step $stepDataStructure, string $expectedImportName)
    {
        $this->assertSame($expectedImportName, $stepDataStructure->getImportName());
    }

    public function getImportNameDataProvider(): array
    {
        return [
            'not present' => [
                'stepDataStructure' => new Step([]),
                'expectedImportName' => '',
            ],
            'not a string' => [
                'stepDataStructure' => new Step([
                    Step::KEY_USE => 123,
                ]),
                'expectedImportName' => '123',
            ],
            'is a string' => [
                'stepDataStructure' => new Step([
                    Step::KEY_USE => 'step_import_name',
                ]),
                'expectedImportName' => 'step_import_name',
            ],
        ];
    }

    /**
     * @dataProvider getDataArrayDataProvider
     */
    public function testGetDataArray(Step $stepDataStructure, array $expectedDataArray)
    {
        $this->assertSame($expectedDataArray, $stepDataStructure->getDataArray());
    }

    public function getDataArrayDataProvider(): array
    {
        return [
            'not present' => [
                'stepDataStructure' => new Step([]),
                'expectedDataArray' => [],
            ],
            'not an array' => [
                'stepDataStructure' => new Step([
                    Step::KEY_DATA => 'data_provider_import_name',
                ]),
                'expectedDataArray' => [],
            ],
            'is an array' => [
                'stepDataStructure' => new Step([
                    Step::KEY_DATA => [
                        'set1' => [
                            'key' => 'value',
                        ],
                    ],
                ]),
                'expectedDataArray' => [
                    'set1' => [
                        'key' => 'value',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider getDataImportNameDataProvider
     */
    public function testGetDataImportName(Step $stepDataStructure, string $expectedDataImportName)
    {
        $this->assertSame($expectedDataImportName, $stepDataStructure->getDataImportName());
    }

    public function getDataImportNameDataProvider(): array
    {
        return [
            'not present' => [
                'stepDataStructure' => new Step([]),
                'expectedDataImportName' => '',
            ],
            'not a string' => [
                'stepDataStructure' => new Step([
                    Step::KEY_DATA => [
                        'set1' => [
                            'key' => 'value',
                        ],
                    ],
                ]),
                'expectedDataImportName' => '',
            ],
            'is a string' => [
                'stepDataStructure' => new Step([
                    Step::KEY_DATA => 'data_provider_import_name',
                ]),
                'expectedDataImportName' => 'data_provider_import_name',
            ],
        ];
    }

    /**
     * @dataProvider getElementStringsDataProvider
     */
    public function testGetElementStrings(Step $stepDataStructure, array $expectedElementStrings)
    {
        $this->assertSame($expectedElementStrings, $stepDataStructure->getElementStrings());
    }

    public function getElementStringsDataProvider(): array
    {
        return [
            'not present' => [
                'stepDataStructure' => new Step([]),
                'expectedElementStrings' => [],
            ],
            'not an array' => [
                'stepDataStructure' => new Step([
                    Step::KEY_ELEMENTS => 'elements',
                ]),
                'expectedElementStrings' => [],
            ],
            'is an array' => [
                'stepDataStructure' => new Step([
                    Step::KEY_ELEMENTS => [
                        'heading' => 'page_import_name.elements.heading',
                    ],
                ]),
                'expectedElementStrings' => [
                    'heading' => 'page_import_name.elements.heading',
                ],
            ],
        ];
    }
}
