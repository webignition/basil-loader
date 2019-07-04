<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Factory;

use Nyholm\Psr7\Uri;
use webignition\BasilModel\Identifier\Identifier;
use webignition\BasilModel\Identifier\IdentifierInterface;
use webignition\BasilModel\Identifier\IdentifierTypes;
use webignition\BasilModel\Page\Page;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\Value;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Factory\IdentifierFactory;
use webignition\BasilParser\Provider\Page\EmptyPageProvider;
use webignition\BasilParser\Provider\Page\PageProviderInterface;
use webignition\BasilParser\Provider\Page\PopulatedPageProvider;
use webignition\BasilParser\Tests\Services\IdentifierFactoryFactory;

class IdentifierFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var IdentifierFactory
     */
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = IdentifierFactoryFactory::create();
    }

    /**
     * @dataProvider createCssSelectorDataProvider
     * @dataProvider createXpathExpressionDataProvider
     * @dataProvider createElementParameterDataProvider
     * @dataProvider createPageModelElementReferenceDataProvider
     * @dataProvider createPageObjectParameterDataProvider
     * @dataProvider createBrowserObjectParameterDataProvider
     */
    public function testCreateSuccess(
        string $identifierString,
        PageProviderInterface $pageProvider,
        IdentifierInterface $expectedIdentifier
    ) {
        $identifier = $this->factory->create($identifierString, $pageProvider);

        $this->assertInstanceOf(IdentifierInterface::class, $identifier);

        if ($identifier instanceof IdentifierInterface) {
            $this->assertEquals($expectedIdentifier, $identifier);

//            $this->assertSame($expectedType, $identifier->getType());
//            $this->assertSame($expectedValue, $identifier->getValue());
//            $this->assertSame($expectedPosition, $identifier->getPosition());
//            $this->assertNull($identifier->getName());
//            $this->assertNull($identifier->getParentIdentifier());
        }
    }

    public function createCssSelectorDataProvider(): array
    {
        return [
            'css id selector' => [
                'identifierString' => '"#element-id"',
                'pageProvider' => new EmptyPageProvider(),
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    new Value(
                        ValueTypes::STRING,
                        '#element-id'
                    ),
                    1
                ),
            ],
            'css class selector, position: null' => [
                'identifierString' => '".listed-item"',
                'pageProvider' => new EmptyPageProvider(),
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    new Value(
                        ValueTypes::STRING,
                        '.listed-item'
                    ),
                    1
                ),
            ],
            'css class selector; position: 1' => [
                'identifierString' => '".listed-item":1',
                'pageProvider' => new EmptyPageProvider(),
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    new Value(
                        ValueTypes::STRING,
                        '.listed-item'
                    ),
                    1
                ),
            ],
            'css class selector; position: 3' => [
                'identifierString' => '".listed-item":3',
                'pageProvider' => new EmptyPageProvider(),
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    new Value(
                        ValueTypes::STRING,
                        '.listed-item'
                    ),
                    3
                ),
            ],
            'css class selector; position: -1' => [
                'identifierString' => '".listed-item":-1',
                'pageProvider' => new EmptyPageProvider(),
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    new Value(
                        ValueTypes::STRING,
                        '.listed-item'
                    ),
                    -1
                ),
            ],
            'css class selector; position: -3' => [
                'identifierString' => '".listed-item":-3',
                'pageProvider' => new EmptyPageProvider(),
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    new Value(
                        ValueTypes::STRING,
                        '.listed-item'
                    ),
                    -3
                ),
            ],
            'css class selector; position: first' => [
                'identifierString' => '".listed-item":first',
                'pageProvider' => new EmptyPageProvider(),
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    new Value(
                        ValueTypes::STRING,
                        '.listed-item'
                    ),
                    1
                ),
            ],
            'css class selector; position: last' => [
                'identifierString' => '".listed-item":last',
                'pageProvider' => new EmptyPageProvider(),
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    new Value(
                        ValueTypes::STRING,
                        '.listed-item'
                    ),
                    -1
                ),
            ],
        ];
    }

    public function createXpathExpressionDataProvider(): array
    {
        return [
            'xpath id selector' => [
                'identifierString' => '"//*[@id="element-id"]"',
                'pageProvider' => new EmptyPageProvider(),
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::XPATH_EXPRESSION,
                    new Value(
                        ValueTypes::STRING,
                        '//*[@id="element-id"]'
                    ),
                    1
                ),
            ],
            'xpath attribute selector, position: null' => [
                'identifierString' => '"//input[@type="submit"]"',
                'pageProvider' => new EmptyPageProvider(),
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::XPATH_EXPRESSION,
                    new Value(
                        ValueTypes::STRING,
                        '//input[@type="submit"]'
                    ),
                    1
                ),
            ],
            'xpath attribute selector; position: 1' => [
                'identifierString' => '"//input[@type="submit"]":1',
                'pageProvider' => new EmptyPageProvider(),
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::XPATH_EXPRESSION,
                    new Value(
                        ValueTypes::STRING,
                        '//input[@type="submit"]'
                    ),
                    1
                ),
            ],
            'xpath attribute selector; position: 3' => [
                'identifierString' => '"//input[@type="submit"]":3',
                'pageProvider' => new EmptyPageProvider(),
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::XPATH_EXPRESSION,
                    new Value(
                        ValueTypes::STRING,
                        '//input[@type="submit"]'
                    ),
                    3
                ),
            ],
            'xpath attribute selector; position: -1' => [
                'identifierString' => '"//input[@type="submit"]":-1',
                'pageProvider' => new EmptyPageProvider(),
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::XPATH_EXPRESSION,
                    new Value(
                        ValueTypes::STRING,
                        '//input[@type="submit"]'
                    ),
                    -1
                ),
            ],
            'xpath attribute selector; position: -3' => [
                'identifierString' => '"//input[@type="submit"]":-3',
                'pageProvider' => new EmptyPageProvider(),
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::XPATH_EXPRESSION,
                    new Value(
                        ValueTypes::STRING,
                        '//input[@type="submit"]'
                    ),
                    -3
                ),
            ],
            'xpath attribute selector; position: first' => [
                'identifierString' => '"//input[@type="submit"]":first',
                'pageProvider' => new EmptyPageProvider(),
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::XPATH_EXPRESSION,
                    new Value(
                        ValueTypes::STRING,
                        '//input[@type="submit"]'
                    ),
                    1
                ),
            ],
            'xpath attribute selector; position: last' => [
                'identifierString' => '"//input[@type="submit"]":last',
                'pageProvider' => new EmptyPageProvider(),
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::XPATH_EXPRESSION,
                    new Value(
                        ValueTypes::STRING,
                        '//input[@type="submit"]'
                    ),
                    -1
                ),
            ],
        ];
    }

    public function createElementParameterDataProvider(): array
    {
        return [
            'element parameter' => [
                'identifierString' => '$elements.name',
                'pageProvider' => new EmptyPageProvider(),
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::ELEMENT_PARAMETER,
                    new ObjectValue(
                        ValueTypes::ELEMENT_PARAMETER,
                        '$elements.name',
                        'elements',
                        'name'
                    )
                ),
            ],
        ];
    }

    public function createPageModelElementReferenceDataProvider(): array
    {
        return [
            'element parameter' => [
                'identifierString' => 'page_import_name.elements.element_name',
                'pageProvider' => new PopulatedPageProvider([
                    'page_import_name' => new Page(
                        new Uri('https://example.com'),
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
        ];
    }

    public function createPageObjectParameterDataProvider(): array
    {
        return [
            'page object parameter' => [
                'identifierString' => '$page.title',
                'pageProvider' => new EmptyPageProvider(),
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::PAGE_OBJECT_PARAMETER,
                    new ObjectValue(
                        ValueTypes::PAGE_OBJECT_PROPERTY,
                        '$page.title',
                        'page',
                        'title'
                    )
                ),
            ],
        ];
    }

    public function createBrowserObjectParameterDataProvider(): array
    {
        return [
            'browser object parameter' => [
                'identifierString' => '$browser.size',
                'pageProvider' => new EmptyPageProvider(),
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::BROWSER_OBJECT_PARAMETER,
                    new ObjectValue(
                        ValueTypes::BROWSER_OBJECT_PROPERTY,
                        '$browser.size',
                        'browser',
                        'size'
                    )
                ),
            ],
        ];
    }

    /**
     * @dataProvider createReferencedElementDataProvider
     */
    public function testCreateWithElementReference(
        string $identifierString,
        array $existingIdentifiers,
        IdentifierInterface $expectedIdentifier
    ) {
        $identifier = $this->factory->createWithElementReference($identifierString, null, $existingIdentifiers);

        $this->assertInstanceOf(IdentifierInterface::class, $identifier);

        if ($identifier instanceof IdentifierInterface) {
            $this->assertEquals($expectedIdentifier, $identifier);
        }
    }

    public function createReferencedElementDataProvider(): array
    {
        $parentIdentifier = new Identifier(
            IdentifierTypes::CSS_SELECTOR,
            new Value(
                ValueTypes::STRING,
                '.parent'
            ),
            null,
            'element_name'
        );

        $existingIdentifiers = [
            'element_name' => $parentIdentifier,
        ];

        return [
            'element reference with css selector, position null, parent identifier not passed' => [
                'identifierString' => '"{{ element_name }} .selector"',
                'existingIdentifiers' => [],
                'expectedIdentifier' => new Identifier(
                    IdentifierTypes::CSS_SELECTOR,
                    new Value(
                        ValueTypes::STRING,
                        '.selector'
                    )
                ),
            ],
            'element reference with css selector, position null' => [
                'identifierString' => '"{{ element_name }} .selector"',
                'existingIdentifiers' => $existingIdentifiers,
                'expectedIdentifier' =>
                    (new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        new Value(
                            ValueTypes::STRING,
                            '.selector'
                        )
                    ))->withParentIdentifier($parentIdentifier),
            ],
            'element reference with css selector, position 1' => [
                'identifierString' => '"{{ element_name }} .selector":1',
                'existingIdentifiers' => $existingIdentifiers,
                'expectedIdentifier' =>
                    (new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        new Value(
                            ValueTypes::STRING,
                            '.selector'
                        )
                    ))->withParentIdentifier($parentIdentifier),
            ],
            'element reference with css selector, position 2' => [
                'identifierString' => '"{{ element_name }} .selector":2',
                'existingIdentifiers' => $existingIdentifiers,
                'expectedIdentifier' =>
                    (new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        new Value(
                            ValueTypes::STRING,
                            '.selector'
                        ),
                        2
                    ))->withParentIdentifier($parentIdentifier),
            ],
            'invalid double element reference with css selector' => [
                'identifierString' => '"{{ element_name }} {{ another_element_name }} .selector"',
                'existingIdentifiers' => $existingIdentifiers,
                'expectedIdentifier' =>
                    (new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        new Value(
                            ValueTypes::STRING,
                            '{{ another_element_name }} .selector'
                        )
                    ))->withParentIdentifier($parentIdentifier),
            ],
            'element reference with xpath expression, position null' => [
                'identifierString' => '"{{ element_name }} //foo"',
                'existingIdentifiers' => $existingIdentifiers,
                'expectedIdentifier' =>
                    (new Identifier(
                        IdentifierTypes::XPATH_EXPRESSION,
                        new Value(
                            ValueTypes::STRING,
                            '//foo'
                        )
                    ))->withParentIdentifier($parentIdentifier),
            ],
            'element reference with xpath expression, position 1' => [
                'identifierString' => '"{{ element_name }} //foo":1',
                'existingIdentifiers' => $existingIdentifiers,
                'expectedIdentifier' =>
                    (new Identifier(
                        IdentifierTypes::XPATH_EXPRESSION,
                        new Value(
                            ValueTypes::STRING,
                            '//foo'
                        )
                    ))->withParentIdentifier($parentIdentifier),
            ],
            'element reference with xpath expression, position 2' => [
                'identifierString' => '"{{ element_name }} //foo":2',
                'existingIdentifiers' => $existingIdentifiers,
                'expectedIdentifier' =>
                    (new Identifier(
                        IdentifierTypes::XPATH_EXPRESSION,
                        new Value(
                            ValueTypes::STRING,
                            '//foo'
                        ),
                        2
                    ))->withParentIdentifier($parentIdentifier),
            ],
        ];
    }

    public function testCreateEmpty()
    {
        $this->assertNull($this->factory->create('', new EmptyPageProvider()));
        $this->assertNull($this->factory->create(' ', new EmptyPageProvider()));
    }

    public function testCreateWithElementReferenceEmpty()
    {
        $this->assertNull($this->factory->createWithElementReference('', null, []));
        $this->assertNull($this->factory->createWithElementReference(' ', null, []));
    }

    public function testCreateForMalformedPageElementReference()
    {
        $this->expectException(MalformedPageElementReferenceException::class);
        $this->expectExceptionMessage('Malformed page element reference "invalid-page-model-element-reference"');

        $this->factory->create('invalid-page-model-element-reference', new EmptyPageProvider());
    }

    public function testCreateForPageElementReferenceForUnknownPage()
    {
        $this->expectException(UnknownPageException::class);
        $this->expectExceptionMessage('Unknown page "import_name"');

        $this->factory->create('import_name.elements.element_name', new EmptyPageProvider());
    }

    public function testCreateForPageElementReferenceForUnknownElement()
    {
        $this->expectException(UnknownPageElementException::class);
        $this->expectExceptionMessage('Unknown page element "element_name" in page "import_name"');

        $this->factory->create(
            'import_name.elements.element_name',
            new PopulatedPageProvider([
                'import_name' => new Page(new Uri('http://example.com'), [])
            ])
        );
    }
}
