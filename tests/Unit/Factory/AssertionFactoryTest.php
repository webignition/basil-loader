<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Factory;

use Nyholm\Psr7\Uri;
use webignition\BasilModel\Assertion\AssertionComparisons;
use webignition\BasilModel\Assertion\AssertionInterface;
use webignition\BasilModel\Page\Page;
use webignition\BasilModel\Value\Value;
use webignition\BasilModel\Value\ValueInterface;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilModel\Identifier\Identifier;
use webignition\BasilModel\Identifier\IdentifierInterface;
use webignition\BasilModel\Identifier\IdentifierTypes;
use webignition\BasilParser\Factory\AssertionFactory;
use webignition\BasilParser\Provider\Page\EmptyPageProvider;
use webignition\BasilParser\Provider\Page\PageProviderInterface;
use webignition\BasilParser\Provider\Page\PopulatedPageProvider;
use webignition\BasilParser\Tests\Services\AssertionFactoryFactory;

class AssertionFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AssertionFactory
     */
    private $assertionFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertionFactory = AssertionFactoryFactory::create();
    }

    /**
     * @dataProvider createFromAssertionString
     */
    public function testCreateFromAssertionString(
        string $assertionString,
        PageProviderInterface $pageProvider,
        IdentifierInterface $expectedIdentifier,
        string $expectedComparison,
        ?ValueInterface $expectedValue
    ) {
        $assertion = $this->assertionFactory->createFromAssertionString($assertionString, $pageProvider);

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
                'pageProvider' => new EmptyPageProvider(),
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
            'simple css selector with element reference, is, scalar value' => [
                'assertionString' => '"{{ reference }} .selector" is "value"',
                'pageProvider' => new EmptyPageProvider(),
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '{{ reference }} .selector'
                ),
                'expectedComparison' => AssertionComparisons::IS,
                'expectedValue' => new Value(
                    ValueTypes::STRING,
                    'value'
                ),
            ],
            'simple css selector, is, data parameter value' => [
                'assertionString' => '".selector" is $data.name',
                'pageProvider' => new EmptyPageProvider(),
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
                'pageProvider' => new EmptyPageProvider(),
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
                'pageProvider' => new EmptyPageProvider(),
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
                'pageProvider' => new EmptyPageProvider(),
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.selector'
                ),
                'expectedComparison' => AssertionComparisons::IS,
                'expectedValue' => null,
            ],
            'simple css selector, is-not, scalar value' => [
                'assertionString' => '".selector" is-not "value"',
                'pageProvider' => new EmptyPageProvider(),
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
                'pageProvider' => new EmptyPageProvider(),
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.selector'
                ),
                'expectedComparison' => AssertionComparisons::IS_NOT,
                'expectedValue' => null,
            ],
            'simple css selector, exists, no value' => [
                'assertionString' => '".selector" exists',
                'pageProvider' => new EmptyPageProvider(),
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.selector'
                ),
                'expectedComparison' => AssertionComparisons::EXISTS,
                'expectedValue' => null,
            ],
            'simple css selector, exists, scalar value is ignored' => [
                'assertionString' => '".selector" exists "value"',
                'pageProvider' => new EmptyPageProvider(),
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.selector'
                ),
                'expectedComparison' => AssertionComparisons::EXISTS,
                'expectedValue' => null,
            ],
            'simple css selector, exists, data parameter value is ignored' => [
                'assertionString' => '".selector" exists $data.name"',
                'pageProvider' => new EmptyPageProvider(),
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.selector'
                ),
                'expectedComparison' => AssertionComparisons::EXISTS,
                'expectedValue' => null,
            ],
            'simple css selector, includes, scalar value' => [
                'assertionString' => '".selector" includes "value"',
                'pageProvider' => new EmptyPageProvider(),
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
                'pageProvider' => new EmptyPageProvider(),
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.selector'
                ),
                'expectedComparison' => AssertionComparisons::INCLUDES,
                'expectedValue' => null,
            ],
            'simple css selector, excludes, scalar value' => [
                'assertionString' => '".selector" excludes "value"',
                'pageProvider' => new EmptyPageProvider(),
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
                'pageProvider' => new EmptyPageProvider(),
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.selector'
                ),
                'expectedComparison' => AssertionComparisons::EXCLUDES,
                'expectedValue' => null,
            ],
            'simple css selector, matches, scalar value' => [
                'assertionString' => '".selector" matches "value"',
                'pageProvider' => new EmptyPageProvider(),
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
                'pageProvider' => new EmptyPageProvider(),
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    '.selector'
                ),
                'expectedComparison' => AssertionComparisons::MATCHES,
                'expectedValue' => null,
            ],
            'comparison-including css selector, is, scalar value' => [
                'assertionString' => '".selector is is-not exists not-exists includes excludes matches foo" is "value"',
                'pageProvider' => new EmptyPageProvider(),
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
                'pageProvider' => new EmptyPageProvider(),
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
                    '"//a[ends-with(@href is exists not-exists matches includes excludes, \".pdf\")]" is "value"',
                'pageProvider' => new EmptyPageProvider(),
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::XPATH_EXPRESSION,
                    '//a[ends-with(@href is exists not-exists matches includes excludes, \".pdf\")]'
                ),
                'expectedComparison' => AssertionComparisons::IS,
                'expectedValue' => new Value(
                    ValueTypes::STRING,
                    'value'
                ),
            ],
            'page model element reference' => [
                'assertionString' => 'page_import_name.elements.element_name is "value"',
                'pageProvider' => new PopulatedPageProvider([
                    'page_import_name' => new Page(
                        new Uri('http://example.com'),
                        [
                            'element_name' => new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                '.selector'
                            )
                        ]
                    )
                ]),
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
        ];
    }

    public function testCreateFromEmptyAssertionString()
    {
        $assertionString = '';

        $assertion = $this->assertionFactory->createFromAssertionString($assertionString, new EmptyPageProvider());

        $this->assertInstanceOf(AssertionInterface::class, $assertion);
        $this->assertSame($assertionString, $assertion->getAssertionString());
        $this->assertNull($assertion->getIdentifier());
        $this->assertSame('', $assertion->getComparison());
        $this->assertNull($assertion->getValue());
    }
}
