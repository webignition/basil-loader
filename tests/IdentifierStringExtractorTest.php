<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests;

use webignition\BasilParser\Factory\Action\InputActionFactory;
use webignition\BasilParser\Factory\AssertionFactory;
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
            'assertion: whole-word selector is value' => [
                'string' => '".selector" is "value"',
                'stopStrings' => AssertionFactory::IDENTIFIER_STRING_STOP_STRINGS,
                'expectedIdentifierString' => '".selector"',
            ],
//            'assertion: selector ending with stop word is value' => [
//                'string' => '".selector is" is "value"',
//                'stopStrings' => AssertionFactory::IDENTIFIER_STRING_STOP_STRINGS,
//                'expectedIdentifierString' => '".selector is"',
//            ],
//            'assertion: selector containing with stop word is value' => [
//                'string' => '".selector is .value" is "value"',
//                'stopStrings' => AssertionFactory::IDENTIFIER_STRING_STOP_STRINGS,
//                'expectedIdentifierString' => '".selector is .value"',
//            ],
//            'assertion: page parameter is value' => [
//                'string' => '$page.title is "value"',
//                'stopStrings' => AssertionFactory::IDENTIFIER_STRING_STOP_STRINGS,
//                'expectedIdentifierString' => '$page.title',
//            ],
//            'assertion: element parameter is value' => [
//                'string' => '$elements.name is "value"',
//                'stopStrings' => AssertionFactory::IDENTIFIER_STRING_STOP_STRINGS,
//                'expectedIdentifierString' => '$elements.name',
//            ],
//            'assertion: page model reference is value' => [
//                'string' => 'page.elements.name is "value"',
//                'stopStrings' => AssertionFactory::IDENTIFIER_STRING_STOP_STRINGS,
//                'expectedIdentifierString' => 'page.elements.name',
//            ],
//            'set action arguments: whole-word selector' => [
//                'string' => '".selector" to "value"',
//                'stopStrings' => [
//                    InputActionFactory::IDENTIFIER_STOP_WORD,
//                ],
//                'expectedIdentifierString' => '".selector"',
//            ],
//            'set action arguments: whole-word selector ending with stop word' => [
//                'string' => '".selector to " to "value"',
//                'stopStrings' => [
//                    InputActionFactory::IDENTIFIER_STOP_WORD,
//                ],
//                'expectedIdentifierString' => '".selector to "',
//            ],
//            'set action arguments: whole-word containing with stop word' => [
//                'string' => '".selector to value" to "value"',
//                'stopStrings' => [
//                    InputActionFactory::IDENTIFIER_STOP_WORD,
//                ],
//                'expectedIdentifierString' => '".selector to value"',
//            ],
//            'set action arguments: no value following stop word' => [
//                'string' => '".selector" to',
//                'stopStrings' => [
//                    InputActionFactory::IDENTIFIER_STOP_WORD,
//                ],
//                'expectedIdentifierString' => '".selector"',
//            ],
//            'assertions: no value following "is" keyword' => [
//                'string' => '".selector" is',
//                'stopStrings' => AssertionFactory::IDENTIFIER_STRING_STOP_STRINGS,
//                'expectedIdentifierString' => '".selector"',
//            ],
//            'assertions: no value following "is-not" keyword' => [
//                'string' => '".selector" is-not',
//                'stopStrings' => AssertionFactory::IDENTIFIER_STRING_STOP_STRINGS,
//                'expectedIdentifierString' => '".selector"',
//            ],
//            'assertions: no value following "includes" keyword' => [
//                'string' => '".selector" includes',
//                'stopStrings' => AssertionFactory::IDENTIFIER_STRING_STOP_STRINGS,
//                'expectedIdentifierString' => '".selector"',
//            ],
//            'assertions: no value following "excludes" keyword' => [
//                'string' => '".selector" excludes',
//                'stopStrings' => AssertionFactory::IDENTIFIER_STRING_STOP_STRINGS,
//                'expectedIdentifierString' => '".selector"',
//            ],
//            'assertions: no value following "matches" keyword' => [
//                'string' => '".selector" matches',
//                'stopStrings' => AssertionFactory::IDENTIFIER_STRING_STOP_STRINGS,
//                'expectedIdentifierString' => '".selector"',
//            ],
        ];
    }
}
