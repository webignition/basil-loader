<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Resolver;

use Nyholm\Psr7\Uri;
use webignition\BasilModel\Assertion\Assertion;
use webignition\BasilModel\Assertion\AssertionComparisons;
use webignition\BasilModel\Assertion\AssertionInterface;
use webignition\BasilModel\Identifier\ElementIdentifier;
use webignition\BasilModel\Identifier\IdentifierCollection;
use webignition\BasilModel\Identifier\IdentifierCollectionInterface;
use webignition\BasilModel\Page\Page;
use webignition\BasilModel\Value\ElementValue;
use webignition\BasilModel\Value\LiteralValue;
use webignition\BasilModel\Value\ObjectNames;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilParser\Exception\UnknownElementException;
use webignition\BasilParser\Provider\Page\EmptyPageProvider;
use webignition\BasilParser\Provider\Page\PageProviderInterface;
use webignition\BasilParser\Provider\Page\PopulatedPageProvider;
use webignition\BasilParser\Resolver\AssertionResolver;
use webignition\BasilParser\Tests\Services\TestIdentifierFactory;

class AssertionResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AssertionResolver
     */
    private $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = AssertionResolver::createResolver();
    }

    /**
     * @dataProvider resolveLeavesAssertionUnchangedDataProvider
     */
    public function testResolvePageElementReferenceExaminedValueLeavesAssertionUnchanged(AssertionInterface $assertion)
    {
        $this->assertSame(
            $assertion,
            $this->resolver->resolvePageElementReferenceExaminedValue($assertion, new EmptyPageProvider())
        );
    }

    /**
     * @dataProvider resolveLeavesAssertionUnchangedDataProvider
     */
    public function testResolveElementParameterExaminedValueLeavesAssertionUnchanged(AssertionInterface $assertion)
    {
        $this->assertSame(
            $assertion,
            $this->resolver->resolveElementParameterExaminedValue($assertion, new IdentifierCollection())
        );
    }

    public function resolveLeavesAssertionUnchangedDataProvider(): array
    {
        return [
            'examined value missing' => [
                'assertion' => new Assertion(
                    '',
                    null,
                    ''
                ),
            ],
            'examined value is not object value' => [
                'assertion' => new Assertion(
                    '',
                    LiteralValue::createStringValue('literal string'),
                    ''
                ),
            ],
            'examined value is not page element reference' => [
                'assertion' => new Assertion(
                    '$page.url is "value"',
                    new ObjectValue(
                        ValueTypes::PAGE_OBJECT_PROPERTY,
                        '$page.url',
                        ObjectNames::PAGE,
                        'url'
                    ),
                    AssertionComparisons::IS,
                    LiteralValue::createStringValue('value')
                ),
            ],
            'examined value is not an element parameter' => [
                'assertion' => new Assertion(
                    '".selector" is "value"',
                    new ElementValue(
                        new ElementIdentifier(
                            LiteralValue::createStringValue('.selector')
                        )
                    ),
                    AssertionComparisons::IS,
                    LiteralValue::createStringValue('value')
                ),
            ],
        ];
    }

    /**
     * @dataProvider resolvePageElementReferenceExaminedValueCreatesNewAssertionDataProvider
     */
    public function testResolvePageElementReferenceExaminedValueCreatesNewAssertion(
        AssertionInterface $assertion,
        PageProviderInterface $pageProvider,
        AssertionInterface $expectedAssertion
    ) {
        $resolvedAssertion = $this->resolver->resolvePageElementReferenceExaminedValue($assertion, $pageProvider);

        $this->assertNotSame($assertion, $resolvedAssertion);
        $this->assertEquals($expectedAssertion, $resolvedAssertion);
    }

    public function resolvePageElementReferenceExaminedValueCreatesNewAssertionDataProvider(): array
    {
        return [
            'page element reference is resolved' => [
                'assertion' => new Assertion(
                    'page_import_name.elements.element_name exists',
                    new ObjectValue(
                        ValueTypes::PAGE_ELEMENT_REFERENCE,
                        'page_import_name.elements.element_name',
                        'page_import_name',
                        'element_name'
                    ),
                    AssertionComparisons::EXISTS
                ),
                'pageProvider' => new PopulatedPageProvider([
                    'page_import_name' => new Page(
                        new Uri('http://example.com/'),
                        new IdentifierCollection([
                            TestIdentifierFactory::createCssElementIdentifier('.selector', 1, 'element_name')
                        ])
                    )
                ]),
                'expectedAssertion' => new Assertion(
                    'page_import_name.elements.element_name exists',
                    new ElementValue(
                        TestIdentifierFactory::createCssElementIdentifier('.selector', 1, 'element_name')
                    ),
                    AssertionComparisons::EXISTS
                ),
            ],
        ];
    }

    /**
     * @dataProvider resolveElementParameterExaminedValueCreatesNewAssertionDataProvider
     */
    public function testResolveElementParameterExaminedValueCreatesNewAssertion(
        AssertionInterface $assertion,
        IdentifierCollectionInterface $identifierCollection,
        AssertionInterface $expectedAssertion
    ) {
        $resolvedAssertion = $this->resolver->resolveElementParameterExaminedValue($assertion, $identifierCollection);

        $this->assertNotSame($assertion, $resolvedAssertion);
        $this->assertEquals($expectedAssertion, $resolvedAssertion);
    }

    public function resolveElementParameterExaminedValueCreatesNewAssertionDataProvider(): array
    {
        return [
            'element parameter is resolved' => [
                'assertion' => new Assertion(
                    '$elements.element_name exists',
                    new ObjectValue(
                        ValueTypes::ELEMENT_PARAMETER,
                        '$elements.element_name',
                        '$elements',
                        'element_name'
                    ),
                    AssertionComparisons::EXISTS
                ),
                'identifierCollection' => new IdentifierCollection([
                    TestIdentifierFactory::createCssElementIdentifier('.selector', 1, 'element_name')
                ]),
                'expectedAssertion' => new Assertion(
                    '$elements.element_name exists',
                    new ElementValue(
                        TestIdentifierFactory::createCssElementIdentifier('.selector', 1, 'element_name')
                    ),
                    AssertionComparisons::EXISTS
                ),
            ],
        ];
    }

    public function testResolveElementParameterExaminedValueThrowsUnknownElementException()
    {
        $assertion = new Assertion(
            '$elements.element_name exists',
            new ObjectValue(
                ValueTypes::ELEMENT_PARAMETER,
                '$elements.element_name',
                '$elements',
                'element_name'
            ),
            AssertionComparisons::EXISTS
        );

        $this->expectException(UnknownElementException::class);
        $this->expectExceptionMessage('Unknown element "element_name"');

        $this->resolver->resolveElementParameterExaminedValue($assertion, new IdentifierCollection());
    }
}
