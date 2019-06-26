<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\DataStructure;

use webignition\BasilParser\DataStructure\Page;

class PageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider getUrlStringDataProvider
     */
    public function testGetUrlString(Page $pageDataStructure, string $expectedUrlString)
    {
        $this->assertSame($expectedUrlString, $pageDataStructure->getUrlString());
    }

    public function getUrlStringDataProvider(): array
    {
        return [
            'not present' => [
                'pageDataStructure' => new Page([]),
                'expectedUrlString' => '',
            ],
            'scalar; integer' => [
                'pageDataStructure' => new Page([
                    Page::KEY_URL => 100,
                ]),
                'expectedUrlString' => '100',
            ],
            'scalar; float' => [
                'pageDataStructure' => new Page([
                    Page::KEY_URL => 3.14,
                ]),
                'expectedUrlString' => '3.14',
            ],
            'scalar; string' => [
                'pageDataStructure' => new Page([
                    Page::KEY_URL => 'http://example.com/',
                ]),
                'expectedUrlString' => 'http://example.com/',
            ],
            'scalar; bool, true' => [
                'pageDataStructure' => new Page([
                    Page::KEY_URL => true,
                ]),
                'expectedUrlString' => '1',
            ],
            'scalar; bool, false' => [
                'pageDataStructure' => new Page([
                    Page::KEY_URL => false,
                ]),
                'expectedUrlString' => '',
            ],
        ];
    }

    /**
     * @dataProvider getElementDataDataProvider
     */
    public function testGetElementData(Page $pageDataStructure, array $expectedElementData)
    {
        $this->assertSame($expectedElementData, $pageDataStructure->getElementData());
    }

    public function getElementDataDataProvider(): array
    {
        return [
            'not present' => [
                'pageDataStructure' => new Page([]),
                'expectedElementData' => [],
            ],
            'not an array' => [
                'pageDataStructure' => new Page([
                    Page::KEY_ELEMENTS => 'string',
                ]),
                'expectedElementData' => [],
            ],
            'empty array' => [
                'pageDataStructure' => new Page([
                    Page::KEY_ELEMENTS => [],
                ]),
                'expectedElementData' => [],
            ],
            'non-empty array' => [
                'pageDataStructure' => new Page([
                    Page::KEY_ELEMENTS => [
                        'title' => '.title',
                    ],
                ]),
                'expectedElementData' => [
                    'title' => '.title',
                ],
            ],
        ];
    }
}
