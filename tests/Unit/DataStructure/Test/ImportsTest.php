<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\DataStructure\Test;

use webignition\BasilParser\DataStructure\Test\Imports;

class ImportsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getStepsDataProvider
     */
    public function testGetSteps(Imports $importsDataStructure, array $expectedSteps)
    {
        $this->assertSame($expectedSteps, $importsDataStructure->getSteps());
    }

    public function getStepsDataProvider(): array
    {
        return [
            'not present' => [
                'importsDataStructure' => new Imports([]),
                'expectedSteps' => [],
            ],
            'not an array' => [
                'importsDataStructure' => new Imports([
                    Imports::KEY_STEPS => 'steps',
                ]),
                'expectedSteps' => [],
            ],
            'empty' => [
                'importsDataStructure' => new Imports([
                    Imports::KEY_STEPS => [],
                ]),
                'expectedSteps' => [],
            ],
            'non-empty' => [
                'importsDataStructure' => new Imports([
                    Imports::KEY_STEPS => [
                        'foo' => 'bar',
                    ],
                ]),
                'expectedSteps' => [
                    'foo' => 'bar',
                ],
            ],
        ];
    }

    /**
     * @dataProvider getPagesDataProvider
     */
    public function testGetPages(Imports $importsDataStructure, array $expectedPages)
    {
        $this->assertSame($expectedPages, $importsDataStructure->getPages());
    }

    public function getPagesDataProvider(): array
    {
        return [
            'not present' => [
                'importsDataStructure' => new Imports([]),
                'expectedPages' => [],
            ],
            'not an array' => [
                'importsDataStructure' => new Imports([
                    Imports::KEY_PAGES => 'pages',
                ]),
                'expectedPages' => [],
            ],
            'empty' => [
                'importsDataStructure' => new Imports([
                    Imports::KEY_PAGES => [],
                ]),
                'expectedPages' => [],
            ],
            'non-empty' => [
                'importsDataStructure' => new Imports([
                    Imports::KEY_PAGES => [
                        'foo' => 'bar',
                    ],
                ]),
                'expectedPages' => [
                    'foo' => 'bar',
                ],
            ],
        ];
    }

    /**
     * @dataProvider getDataProvidersDataProvider
     */
    public function testGetDataProviders(Imports $importsDataStructure, array $expectedPages)
    {
        $this->assertSame($expectedPages, $importsDataStructure->getDataProviders());
    }

    public function getDataProvidersDataProvider(): array
    {
        return [
            'not present' => [
                'importsDataStructure' => new Imports([]),
                'expectedDataProviders' => [],
            ],
            'not an array' => [
                'importsDataStructure' => new Imports([
                    Imports::KEY_DATA_PROVIDERS => 'data providers',
                ]),
                'expectedDataProviders' => [],
            ],
            'empty' => [
                'importsDataStructure' => new Imports([
                    Imports::KEY_DATA_PROVIDERS => [],
                ]),
                'expectedDataProviders' => [],
            ],
            'non-empty' => [
                'importsDataStructure' => new Imports([
                    Imports::KEY_DATA_PROVIDERS => [
                        'foo' => 'bar',
                    ],
                ]),
                'expectedDataProviders' => [
                    'foo' => 'bar',
                ],
            ],
        ];
    }
}
