<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Factory;

use webignition\BasilParser\Factory\AssertionFactory;
use webignition\BasilParser\Model\Assertion\AssertionComparisons;
use webignition\BasilParser\Model\Assertion\AssertionInterface;
use webignition\BasilParser\Model\Value\Value;
use webignition\BasilParser\Model\Value\ValueInterface;
use webignition\BasilParser\Model\Value\ValueTypes;
use webignition\BasilParser\Model\Identifier\Identifier;
use webignition\BasilParser\Model\Identifier\IdentifierInterface;
use webignition\BasilParser\Model\Identifier\IdentifierTypes;

class AssertionFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AssertionFactory
     */
    private $assertionFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertionFactory = new AssertionFactory();
    }

    /**
     * @dataProvider createFromAssertionString
     */
    public function testCreateFromAssertionString(
        string $assertionString,
        IdentifierInterface $expectedIdentifier,
        string $expectedComparison,
        ?ValueInterface $expectedValue
    ) {
        $assertion = $this->assertionFactory->createFromAssertionString($assertionString);

        $this->assertInstanceOf(AssertionInterface::class, $assertion);
        $this->assertSame($assertionString, $assertion->getAssertionString());
        $this->assertEquals($expectedIdentifier, $assertion->getIdentifier());
        $this->assertSame($expectedComparison, $assertion->getComparison());
        $this->assertEquals($expectedValue, $assertion->getValue());
    }

    public function createFromAssertionString(): array
    {
        return [
            'simple css selector, is, scalar value' => [
                'assertionString' => '".selector" is "value"',
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.selector'
                ),
                'expectedComparison' => AssertionComparisons::IS,
                'expectedValue' => new Value(
                    ValueTypes::STRING,
                    'value'
                ),
            ],
            'simple css selector, is, data parameter value' => [
                'assertionString' => '".selector" is $data.name',
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.selector'
                ),
                'expectedComparison' => AssertionComparisons::IS,
                'expectedValue' => new Value(
                    ValueTypes::DATA_PARAMETER,
                    '$data.name'
                ),
            ],
            'simple css selector, is, element parameter value' => [
                'actionString' => '".selector" is $elements.name',
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.selector'
                ),
                'expectedComparison' => AssertionComparisons::IS,
                'expectedValue' => new Value(
                    ValueTypes::ELEMENT_PARAMETER,
                    '$elements.name'
                ),
            ],
            'simple css selector, is, escaped quotes scalar value' => [
                'assertionString' => '".selector" is "\"value\""',
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.selector'
                ),
                'expectedComparison' => AssertionComparisons::IS,
                'expectedValue' => new Value(
                    ValueTypes::STRING,
                    '"value"'
                ),
            ],
            'simple css selector, is, lacking value' => [
                'assertionString' => '".selector" is',
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.selector'
                ),
                'expectedComparison' => AssertionComparisons::IS,
                'expectedValue' => null,
            ],
            'simple css selector, is-not, scalar value' => [
                'assertionString' => '".selector" is-not "value"',
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.selector'
                ),
                'expectedComparison' => AssertionComparisons::IS_NOT,
                'expectedValue' => new Value(
                    ValueTypes::STRING,
                    'value'
                ),
            ],
            'simple css selector, is-not, lacking value' => [
                'assertionString' => '".selector" is-not',
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.selector'
                ),
                'expectedComparison' => AssertionComparisons::IS_NOT,
                'expectedValue' => null,
            ],
            'simple css selector, exists, no value' => [
                'assertionString' => '".selector" exists',
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.selector'
                ),
                'expectedComparison' => AssertionComparisons::EXISTS,
                'expectedValue' => null,
            ],
            'simple css selector, exists, scalar value is ignored' => [
                'assertionString' => '".selector" exists "value"',
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.selector'
                ),
                'expectedComparison' => AssertionComparisons::EXISTS,
                'expectedValue' => null,
            ],
            'simple css selector, exists, data parameter value is ignored' => [
                'assertionString' => '".selector" exists $data.name"',
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.selector'
                ),
                'expectedComparison' => AssertionComparisons::EXISTS,
                'expectedValue' => null,
            ],
            'simple css selector, includes, scalar value' => [
                'assertionString' => '".selector" includes "value"',
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.selector'
                ),
                'expectedComparison' => AssertionComparisons::INCLUDES,
                'expectedValue' => new Value(
                    ValueTypes::STRING,
                    'value'
                ),
            ],
            'simple css selector, includes, lacking value' => [
                'assertionString' => '".selector" includes',
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.selector'
                ),
                'expectedComparison' => AssertionComparisons::INCLUDES,
                'expectedValue' => null,
            ],
            'simple css selector, excludes, scalar value' => [
                'assertionString' => '".selector" excludes "value"',
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.selector'
                ),
                'expectedComparison' => AssertionComparisons::EXCLUDES,
                'expectedValue' => new Value(
                    ValueTypes::STRING,
                    'value'
                ),
            ],
            'simple css selector, excludes, lacking value' => [
                'assertionString' => '".selector" excludes',
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.selector'
                ),
                'expectedComparison' => AssertionComparisons::EXCLUDES,
                'expectedValue' => null,
            ],
            'simple css selector, matches, scalar value' => [
                'assertionString' => '".selector" matches "value"',
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.selector'
                ),
                'expectedComparison' => AssertionComparisons::MATCHES,
                'expectedValue' => new Value(
                    ValueTypes::STRING,
                    'value'
                ),
            ],
            'simple css selector, matches, lacking value' => [
                'assertionString' => '".selector" matches',
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.selector'
                ),
                'expectedComparison' => AssertionComparisons::MATCHES,
                'expectedValue' => null,
            ],
            'comparison-including css selector, is, scalar value' => [
                'assertionString' => '".selector is is-not exists not-exists includes excludes matches foo" is "value"',
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.selector is is-not exists not-exists includes excludes matches foo'
                ),
                'expectedComparison' => AssertionComparisons::IS,
                'expectedValue' => new Value(
                    ValueTypes::STRING,
                    'value'
                ),
            ],
            'simple xpath expression, is, scalar value' => [
                'assertionString' => '"//foo" is "value"',
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::XPATH_EXPRESSION,
                    '//foo'
                ),
                'expectedComparison' => AssertionComparisons::IS,
                'expectedValue' => new Value(
                    ValueTypes::STRING,
                    'value'
                ),
            ],
            'comparison-including non-simple xpath expression, is, scalar value' => [
                'assertionString' =>
                    '"//a[ends-with(@href is exists not-exists matches includes excludes, ".pdf")]" is "value"',
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::XPATH_EXPRESSION,
                    '//a[ends-with(@href is exists not-exists matches includes excludes, ".pdf")]'
                ),
                'expectedComparison' => AssertionComparisons::IS,
                'expectedValue' => new Value(
                    ValueTypes::STRING,
                    'value'
                ),
            ],
        ];
    }
}
