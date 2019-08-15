<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Resolver;

use Nyholm\Psr7\Uri;
use webignition\BasilModel\Identifier\Identifier;
use webignition\BasilModel\Identifier\IdentifierCollection;
use webignition\BasilModel\Identifier\IdentifierCollectionInterface;
use webignition\BasilModel\Identifier\IdentifierInterface;
use webignition\BasilModel\Identifier\IdentifierTypes;
use webignition\BasilModel\Page\Page;
use webignition\BasilModel\Value\LiteralValue;
use webignition\BasilModel\Value\ObjectNames;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilParser\Exception\UnknownElementException;
use webignition\BasilParser\Provider\Page\EmptyPageProvider;
use webignition\BasilParser\Provider\Page\PageProviderInterface;
use webignition\BasilParser\Provider\Page\PopulatedPageProvider;
use webignition\BasilParser\Resolver\IdentifierResolver;
use webignition\BasilParser\Tests\Services\TestIdentifierFactory;

class IdentifierResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var IdentifierResolver
     */
    private $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = IdentifierResolver::createResolver();
    }

    /**
     * @dataProvider resolveNonResolvableDataProvider
     */
    public function testResolvePageElementReferenceNonResolvable(IdentifierInterface $identifier)
    {
        $resolvedIdentifier = $this->resolver->resolvePageElementReference($identifier, new EmptyPageProvider());

        $this->assertSame($identifier, $resolvedIdentifier);
    }

    /**
     * @dataProvider resolveNonResolvableDataProvider
     */
    public function testResolveElementParameterNonResolvable(IdentifierInterface $identifier)
    {
        $resolvedIdentifier = $this->resolver->resolveElementParameter($identifier, new IdentifierCollection());

        $this->assertSame($identifier, $resolvedIdentifier);
    }

    public function resolveNonResolvableDataProvider(): array
    {
        return [
            'wrong identifier type' => [
                'identifier' => TestIdentifierFactory::createCssElementIdentifier('.selector'),
            ],
            'wrong value type' => [
                'identifier' => new Identifier(
                    IdentifierTypes::PAGE_ELEMENT_REFERENCE,
                    LiteralValue::createStringValue('value')
                ),
            ],
        ];
    }

    /**
     * @dataProvider resolvePageElementReferenceIsResolvedDataProvider
     */
    public function testResolvePageElementReferenceIsResolved(
        IdentifierInterface $identifier,
        PageProviderInterface $pageProvider,
        IdentifierInterface $expectedIdentifier
    ) {
        $resolvedIdentifier = $this->resolver->resolvePageElementReference($identifier, $pageProvider);

        $this->assertEquals($expectedIdentifier, $resolvedIdentifier);
    }

    public function resolvePageElementReferenceIsResolvedDataProvider(): array
    {
        $cssElementIdentifier = TestIdentifierFactory::createCssElementIdentifier('.selector');

        $cssElementIdentifierWithName = $cssElementIdentifier->withName('element_name');

        return [
            'resolvable page element reference' => [
                'identifier' => new Identifier(
                    IdentifierTypes::PAGE_ELEMENT_REFERENCE,
                    new ObjectValue(
                        ValueTypes::PAGE_ELEMENT_REFERENCE,
                        'page_import_name.elements.element_name',
                        'page_import_name',
                        'element_name'
                    )
                ),
                'pageProvider' => new PopulatedPageProvider([
                    'page_import_name' => new Page(
                        new Uri('http://example.com/'),
                        new IdentifierCollection([
                            $cssElementIdentifierWithName,
                        ])
                    )
                ]),
                'expectedIdentifier' => $cssElementIdentifierWithName,
            ],
        ];
    }

    /**
     * @dataProvider resolveElementParameterIsResolvedDataProvider
     */
    public function testResolveElementParameterIsResolved(
        IdentifierInterface $identifier,
        IdentifierCollectionInterface $identifierCollection,
        IdentifierInterface $expectedIdentifier
    ) {
        $resolvedIdentifier = $this->resolver->resolveElementParameter($identifier, $identifierCollection);

        $this->assertEquals($expectedIdentifier, $resolvedIdentifier);
    }

    public function resolveElementParameterIsResolvedDataProvider(): array
    {
        $cssElementIdentifier = TestIdentifierFactory::createCssElementIdentifier('.selector');

        $cssElementIdentifierWithName = $cssElementIdentifier->withName('element_name');

        return [
            'element parameter' => [
                'identifier' => new Identifier(
                    IdentifierTypes::ELEMENT_PARAMETER,
                    new ObjectValue(
                        ValueTypes::ELEMENT_PARAMETER,
                        '$elements.element_name',
                        ObjectNames::ELEMENT,
                        'element_name'
                    )
                ),
                'identifierCollection' => new IdentifierCollection([
                    $cssElementIdentifierWithName
                ]),
                'expectedIdentifier' => $cssElementIdentifierWithName,
            ],
        ];
    }

    public function testResolveElementParameterThrowsUnknownElementException()
    {
        $identifier = new Identifier(
            IdentifierTypes::ELEMENT_PARAMETER,
            new ObjectValue(
                ValueTypes::ELEMENT_PARAMETER,
                '$elements.element_name',
                ObjectNames::ELEMENT,
                'element_name'
            )
        );

        $this->expectException(UnknownElementException::class);
        $this->expectExceptionMessage('Unknown element "element_name"');

        $this->resolver->resolveElementParameter($identifier, new IdentifierCollection());
    }
}
