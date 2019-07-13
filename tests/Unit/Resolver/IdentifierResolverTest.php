<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Resolver;

use Nyholm\Psr7\Uri;
use webignition\BasilModel\Identifier\Identifier;
use webignition\BasilModel\Identifier\IdentifierInterface;
use webignition\BasilModel\Identifier\IdentifierTypes;
use webignition\BasilModel\Page\Page;
use webignition\BasilModel\Value\Value;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilParser\Provider\Page\EmptyPageProvider;
use webignition\BasilParser\Provider\Page\PageProviderInterface;
use webignition\BasilParser\Provider\Page\PopulatedPageProvider;
use webignition\BasilParser\Resolver\IdentifierResolver;
use webignition\BasilParser\Tests\Services\IdentifierResolverFactory;

class IdentifierResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var IdentifierResolver
     */
    private $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = IdentifierResolverFactory::create();
    }

    /**
     * @dataProvider resolveDataProvider
     */
    public function testResolve(
        IdentifierInterface $identifier,
        PageProviderInterface $pageProvider,
        IdentifierInterface $expectedIdentifier
    ) {
        $resolvedIdentifier = $this->resolver->resolve($identifier, $pageProvider);

        $this->assertEquals($expectedIdentifier, $resolvedIdentifier);
    }

    public function resolveDataProvider(): array
    {
        return [
            'non-resolvable' => [
                'identifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    new Value(
                        ValueTypes::STRING,
                        '.selector'
                    )
                ),
                'pageProvider' => new EmptyPageProvider(),
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    new Value(
                        ValueTypes::STRING,
                        '.selector'
                    )
                ),
            ],
            'resolvable, no name' => [
                'identifier' => new Identifier(
                    IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                    new Value(
                        ValueTypes::STRING,
                        'page_import_name.elements.element_name'
                    )
                ),
                'pageProvider' => new PopulatedPageProvider([
                    'page_import_name' => new Page(
                        new Uri('http://example.com/'),
                        [
                            'element_name' => new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                new Value(
                                    ValueTypes::STRING,
                                    '.selector'
                                )
                            )
                        ]
                    )
                ]),
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    new Value(
                        ValueTypes::STRING,
                        '.selector'
                    )
                ),
            ],
            'resolvable, has name' => [
                'identifier' => new Identifier(
                    IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                    new Value(
                        ValueTypes::STRING,
                        'page_import_name.elements.element_name'
                    ),
                    null,
                    'identifier_name'
                ),
                'pageProvider' => new PopulatedPageProvider([
                    'page_import_name' => new Page(
                        new Uri('http://example.com/'),
                        [
                            'element_name' => new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                new Value(
                                    ValueTypes::STRING,
                                    '.selector'
                                )
                            )
                        ]
                    )
                ]),
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    new Value(
                        ValueTypes::STRING,
                        '.selector'
                    ),
                    1,
                    'identifier_name'
                ),
            ],
        ];
    }
}
