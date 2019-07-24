<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Resolver;

use Nyholm\Psr7\Uri;
use webignition\BasilModel\Identifier\ElementIdentifier;
use webignition\BasilModel\Identifier\IdentifierCollection;
use webignition\BasilModel\Page\Page;
use webignition\BasilModel\Value\LiteralValue;
use webignition\BasilModel\Value\ObjectNames;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilModel\Value\ValueInterface;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilParser\Provider\Page\EmptyPageProvider;
use webignition\BasilParser\Provider\Page\PageProviderInterface;
use webignition\BasilParser\Provider\Page\PopulatedPageProvider;
use webignition\BasilParser\Resolver\PageElementReferenceObjectValueResolver;

class PageElementReferenceObjectValueResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PageElementReferenceObjectValueResolver
     */
    private $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = new PageElementReferenceObjectValueResolver();
    }

    /**
     * @dataProvider resolveLeavesValueUnchangedDataProvider
     */
    public function testResolveLeavesValueUnchanged(ObjectValueInterface $value)
    {
        $resolvedValue = $this->resolver->resolve($value, new EmptyPageProvider());

        $this->assertSame($value, $resolvedValue);
    }

    public function resolveLeavesValueUnchangedDataProvider(): array
    {
        return [
            'data parameter' => [
                'value' => new ObjectValue(
                    ValueTypes::DATA_PARAMETER,
                    '$data.key',
                    ObjectNames::DATA,
                    'key'
                ),
            ],
            'element parameter' => [
                'value' => new ObjectValue(
                    ValueTypes::ELEMENT_PARAMETER,
                    '$elements.key',
                    ObjectNames::ELEMENT,
                    'key'
                ),
            ],
            'page object property' => [
                'value' => new ObjectValue(
                    ValueTypes::PAGE_OBJECT_PROPERTY,
                    '$page.url',
                    ObjectNames::PAGE,
                    'url'
                ),
            ],
            'browser object property' => [
                'value' => new ObjectValue(
                    ValueTypes::BROWSER_OBJECT_PROPERTY,
                    '$browser.size',
                    ObjectNames::BROWSER,
                    'size'
                ),
            ],
        ];
    }

    /**
     * @dataProvider resolveDataProvider
     */
    public function testResolve(
        ObjectValueInterface $value,
        PageProviderInterface $pageProvider,
        ValueInterface $expectedValue
    ) {
        $resolvedValue = $this->resolver->resolve($value, $pageProvider);

        $this->assertEquals($expectedValue, $resolvedValue);
    }

    public function resolveDataProvider(): array
    {
        return [
            'resolvable' => [
                'value' => new ObjectValue(
                    ValueTypes::PAGE_ELEMENT_REFERENCE,
                    'page_import_name.elements.element_name',
                    'page_import_name',
                    'element_name'
                ),
                'pageProvider' => new PopulatedPageProvider([
                    'page_import_name' => new Page(
                        new Uri('http://example.com/'),
                        new IdentifierCollection([
                            (new ElementIdentifier(
                                LiteralValue::createCssSelectorValue('.selector')
                            ))->withName('element_name')
                        ])
                    )
                ]),
                'expectedValue' => LiteralValue::createCssSelectorValue('.selector'),
            ],
        ];
    }
}
