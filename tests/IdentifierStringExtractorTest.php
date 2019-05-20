<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests;

use webignition\BasilParser\IdentifierStringExtractor;

class IdentifierStringExtractorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var IdentifierStringExtractor
     */
    private $identifierStringExtractor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->identifierStringExtractor = new IdentifierStringExtractor();
    }

    /**
     * @dataProvider extractFromStartDataProvider
     */
    public function testExtractFromStart(string $string, array $stopStrings, string $expectedIdentifierString)
    {
        $identifierString = $this->identifierStringExtractor->extractFromStart($string, $stopStrings);

        $this->assertSame($expectedIdentifierString, $identifierString);
    }

    public function extractFromStartDataProvider(): array
    {
        return [
            'empty' => [
                'string' => '',
                'stopStrings' => [],
                'expectedIdentifierString' => '',
            ],
            'assertion: whole-word selector is value' => [
                'string' => '".selector" is "value"',
                'stopStrings' => [
                    ' is ',
                ],
                'expectedIdentifierString' => '".selector"',
            ],
            'assertion: selector ending with stop word is value' => [
                'string' => '".selector is" is "value"',
                'stopStrings' => [
                    ' is ',
                ],
                'expectedIdentifierString' => '".selector is"',
            ],
            'assertion: selector containing with stop word is value' => [
                'string' => '".selector is .value" is "value"',
                'stopStrings' => [
                    ' is ',
                ],
                'expectedIdentifierString' => '".selector is .value"',
            ],
            'set action arguments: whole-word selector' => [
                'string' => '".selector" to "value"',
                'stopStrings' => [
                    ' to ',
                ],
                'expectedIdentifierString' => '".selector"',
            ],
            'set action arguments: whole-word selector ending with stop word' => [
                'string' => '".selector to " to "value"',
                'stopStrings' => [
                    ' to ',
                ],
                'expectedIdentifierString' => '".selector to "',
            ],
            'set action arguments: whole-word containing with stop word' => [
                'string' => '".selector to value" to "value"',
                'stopStrings' => [
                    ' to ',
                ],
                'expectedIdentifierString' => '".selector to value"',
            ],
        ];
    }
}
