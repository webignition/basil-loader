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
use webignition\BasilModel\Page\Page;
use webignition\BasilModel\Value\ElementValue;
use webignition\BasilModel\Value\LiteralValue;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilParser\Provider\Page\EmptyPageProvider;
use webignition\BasilParser\Provider\Page\PageProviderInterface;
use webignition\BasilParser\Provider\Page\PopulatedPageProvider;
use webignition\BasilParser\Resolver\AssertionResolver;

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
    public function testResolveLeavesAssertionUnchanged(AssertionInterface $assertion)
    {
        $this->assertSame(
            $assertion,
            $this->resolver->resolve($assertion, new EmptyPageProvider())
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
        ];
    }

    /**
     * @dataProvider resolveCreatesNewAssertionDataProvider
     */
    public function testResolveCreatesNewAssertion(
        AssertionInterface $assertion,
        PageProviderInterface $pageProvider,
        AssertionInterface $expectedAssertion
    ) {
        $resolvedAssertion = $this->resolver->resolve($assertion, $pageProvider);

        $this->assertNotSame($assertion, $resolvedAssertion);
        $this->assertEquals($expectedAssertion, $resolvedAssertion);
    }

    public function resolveCreatesNewAssertionDataProvider(): array
    {
        return [
            'is resolved' => [
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
                            new ElementIdentifier(
                                LiteralValue::createCssSelectorValue('.selector'),
                                1,
                                'element_name'
                            )
                        ])
                    )
                ]),
                'expectedAssertion' => new Assertion(
                    'page_import_name.elements.element_name exists',
                    new ElementValue(
                        new ElementIdentifier(
                            LiteralValue::createCssSelectorValue('.selector'),
                            1,
                            'element_name'
                        )
                    ),
                    AssertionComparisons::EXISTS
                ),
            ],
        ];
    }
}
