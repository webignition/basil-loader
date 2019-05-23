<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\IdentifierStringExtractor;

use webignition\BasilParser\IdentifierStringExtractor\IdentifierStringExtractor;

class IdentifierStringExtractorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \webignition\BasilParser\IdentifierStringExtractor\IdentifierStringExtractor
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
    public function testExtractFromStart(string $string, string $expectedIdentifierString)
    {
        $identifierString = $this->identifierStringExtractor->extractFromStart($string);

        $this->assertSame($expectedIdentifierString, $identifierString);
    }

    public function extractFromStartDataProvider(): array
    {
        return [
            'assertion: whole-word quoted identifier' => [
                'string' => '".selector" is "value"',
                'expectedIdentifierString' => '".selector"',
            ],
            'assertion: quoted identifier ending with comparison' => [
                'string' => '".selector is" is "value"',
                'expectedIdentifierString' => '".selector is"',
            ],
            'assertion: quoted identifier containing comparison and value' => [
                'string' => '".selector is value" is "value"',
                'expectedIdentifierString' => '".selector is value"',
            ],
            'assertion: whole-word quoted identifier with encapsulating escaped quotes' => [
                'string' => '"\".selector\"" is "value"',
                'expectedIdentifierString' => '"\".selector\""',
            ],
            'assertion: quoted quoted identifier containing escaped quotes' => [
                'string' => '".selector \".is\"" is "value"',
                'expectedIdentifierString' => '".selector \".is\""',
            ],
            'assertion: page parameter is value' => [
                'string' => '$page.title is "value"',
                'expectedIdentifierString' => '$page.title',
            ],
            'assertion: element parameter is value' => [
                'string' => '$elements.name is "value"',
                'expectedIdentifierString' => '$elements.name',
            ],
            'assertion: page model reference is value' => [
                'string' => 'page.elements.name is "value"',
                'expectedIdentifierString' => 'page.elements.name',
            ],
            'set action arguments: whole-word selector' => [
                'string' => '".selector" to "value"',
                'expectedIdentifierString' => '".selector"',
            ],
            'set action arguments: whole-word selector ending with stop word' => [
                'string' => '".selector to " to "value"',
                'expectedIdentifierString' => '".selector to "',
            ],
            'set action arguments: whole-word containing with stop word' => [
                'string' => '".selector to value" to "value"',
                'expectedIdentifierString' => '".selector to value"',
            ],
            'set action arguments: no value following stop word' => [
                'string' => '".selector" to',
                'expectedIdentifierString' => '".selector"',
            ],
            'assertion: no value following "is" keyword' => [
                'string' => '".selector" is',
                'expectedIdentifierString' => '".selector"',
            ],
            'assertion: no value following "is-not" keyword' => [
                'string' => '".selector" is-not',
                'expectedIdentifierString' => '".selector"',
            ],
            'assertion: no value following "includes" keyword' => [
                'string' => '".selector" includes',
                'expectedIdentifierString' => '".selector"',
            ],
            'assertion: no value following "excludes" keyword' => [
                'string' => '".selector" excludes',
                'expectedIdentifierString' => '".selector"',
            ],
            'assertion: no value following "matches" keyword' => [
                'string' => '".selector" matches',
                'expectedIdentifierString' => '".selector"',
            ],
            'whole-word quoted identifier only' => [
                'string' => '".selector"',
                'expectedIdentifierString' => '".selector"',
            ],
            'page parameter only' => [
                'string' => '$page.title',
                'expectedIdentifierString' => '$page.title',
            ],
            'assertion: page model reference only' => [
                'string' => 'page.elements.name',
                'expectedIdentifierString' => 'page.elements.name',
            ],
        ];
    }
}
