<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Factory;

use Nyholm\Psr7\Uri;
use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Factory\IdentifierFactory;
use webignition\BasilParser\Model\Identifier\Identifier;
use webignition\BasilParser\Model\Identifier\IdentifierInterface;
use webignition\BasilParser\Model\Identifier\IdentifierTypes;
use webignition\BasilParser\Model\Page\Page;

class IdentifierFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var IdentifierFactory
     */
    private $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new IdentifierFactory();
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
        array $pages,
        string $expectedType,
        string $expectedValue,
        int $expectedPosition
    ) {
        $identifier = $this->factory->create($identifierString, $pages);

        $this->assertInstanceOf(IdentifierInterface::class, $identifier);

        if ($identifier instanceof IdentifierInterface) {
            $this->assertSame($expectedType, $identifier->getType());
            $this->assertSame($expectedValue, $identifier->getValue());
            $this->assertSame($expectedPosition, $identifier->getPosition());
            $this->assertNull($identifier->getName());
            $this->assertNull($identifier->getParentIdentifier());
        }
    }

    public function createCssSelectorDataProvider(): array
    {
        return [
            'css id selector' => [
                'identifierString' => '"#element-id"',
                'pages' => [],
                'expectedType' => IdentifierTypes::CSS_SELECTOR,
                'expectedValue' => '#element-id',
                'expectedPosition' => 1,
            ],
            'css class selector, position: null' => [
                'identifierString' => '".listed-item"',
                'pages' => [],
                'expectedType' => IdentifierTypes::CSS_SELECTOR,
                'expectedValue' => '.listed-item',
                'expectedPosition' => 1,
            ],
            'css class selector; position: 1' => [
                'identifierString' => '".listed-item":1',
                'pages' => [],
                'expectedType' => IdentifierTypes::CSS_SELECTOR,
                'expectedValue' => '.listed-item',
                'expectedPosition' => 1,
            ],
            'css class selector; position: 3' => [
                'identifierString' => '".listed-item":3',
                'pages' => [],
                'expectedType' => IdentifierTypes::CSS_SELECTOR,
                'expectedValue' => '.listed-item',
                'expectedPosition' => 3,
            ],
            'css class selector; position: -1' => [
                'identifierString' => '".listed-item":-1',
                'pages' => [],
                'expectedType' => IdentifierTypes::CSS_SELECTOR,
                'expectedValue' => '.listed-item',
                'expectedPosition' => -1,
            ],
            'css class selector; position: -3' => [
                'identifierString' => '".listed-item":-3',
                'pages' => [],
                'expectedType' => IdentifierTypes::CSS_SELECTOR,
                'expectedValue' => '.listed-item',
                'expectedPosition' => -3,
            ],
            'css class selector; position: first' => [
                'identifierString' => '".listed-item":first',
                'pages' => [],
                'expectedType' => IdentifierTypes::CSS_SELECTOR,
                'expectedValue' => '.listed-item',
                'expectedPosition' => 1,
            ],
            'css class selector; position: last' => [
                'identifierString' => '".listed-item":last',
                'pages' => [],
                'expectedType' => IdentifierTypes::CSS_SELECTOR,
                'expectedValue' => '.listed-item',
                'expectedPosition' => -1,
            ],
        ];
    }

    public function createXpathExpressionDataProvider(): array
    {
        return [
            'xpath id selector' => [
                'identifierString' => '"//*[@id="element-id"]"',
                'pages' => [],
                'expectedType' => IdentifierTypes::XPATH_EXPRESSION,
                'expectedValue' => '//*[@id="element-id"]',
                'expectedPosition' => 1,
            ],
            'xpath attribute selector, position: null' => [
                'identifierString' => '"//input[@type="submit"]"',
                'pages' => [],
                'expectedType' => IdentifierTypes::XPATH_EXPRESSION,
                'expectedValue' => '//input[@type="submit"]',
                'expectedPosition' => 1,
            ],
            'xpath attribute selector; position: 1' => [
                'identifierString' => '"//input[@type="submit"]":1',
                'pages' => [],
                'expectedType' => IdentifierTypes::XPATH_EXPRESSION,
                'expectedValue' => '//input[@type="submit"]',
                'expectedPosition' => 1,
            ],
            'xpath attribute selector; position: 3' => [
                'identifierString' => '"//input[@type="submit"]":3',
                'pages' => [],
                'expectedType' => IdentifierTypes::XPATH_EXPRESSION,
                'expectedValue' => '//input[@type="submit"]',
                'expectedPosition' => 3,
            ],
            'xpath attribute selector; position: -1' => [
                'identifierString' => '"//input[@type="submit"]":-1',
                'pages' => [],
                'expectedType' => IdentifierTypes::XPATH_EXPRESSION,
                'expectedValue' => '//input[@type="submit"]',
                'expectedPosition' => -1,
            ],
            'xpath attribute selector; position: -3' => [
                'identifierString' => '"//input[@type="submit"]":-3',
                'pages' => [],
                'expectedType' => IdentifierTypes::XPATH_EXPRESSION,
                'expectedValue' => '//input[@type="submit"]',
                'expectedPosition' => -3,
            ],
            'xpath attribute selector; position: first' => [
                'identifierString' => '"//input[@type="submit"]":first',
                'pages' => [],
                'expectedType' => IdentifierTypes::XPATH_EXPRESSION,
                'expectedValue' => '//input[@type="submit"]',
                'expectedPosition' => 1,
            ],
            'xpath attribute selector; position: last' => [
                'identifierString' => '"//input[@type="submit"]":last',
                'pages' => [],
                'expectedType' => IdentifierTypes::XPATH_EXPRESSION,
                'expectedValue' => '//input[@type="submit"]',
                'expectedPosition' => -1,
            ],
        ];
    }

    public function createElementParameterDataProvider(): array
    {
        return [
            'element parameter' => [
                'identifierString' => '$element.name',
                'pages' => [],
                'expectedType' => IdentifierTypes::ELEMENT_PARAMETER,
                'expectedValue' => '$element.name',
                'expectedPosition' => 1,
            ],
        ];
    }

    public function createPageModelElementReferenceDataProvider(): array
    {
        return [
            'element parameter' => [
                'identifierString' => 'page_import_name.elements.element_name',
                'pages' => [
                    'page_import_name' => new Page(
                        new Uri('https://example.com'),
                        [
                            'element_name' => new Identifier(
                                IdentifierTypes::CSS_SELECTOR,
                                '.selector'
                            )
                        ]
                    )
                ],
                'expectedType' => IdentifierTypes::CSS_SELECTOR,
                'expectedValue' => '.selector',
                'expectedPosition' => 1,
            ],
        ];
    }

    public function createPageObjectParameterDataProvider(): array
    {
        return [
            'page object parameter' => [
                'identifierString' => '$page.title',
                'pages' => [],
                'expectedType' => IdentifierTypes::PAGE_OBJECT_PARAMETER,
                'expectedValue' => '$page.title',
                'expectedPosition' => 1,
            ],
        ];
    }

    public function createBrowserObjectParameterDataProvider(): array
    {
        return [
            'browser object parameter' => [
                'identifierString' => '$browser.url',
                'pages' => [],
                'expectedType' => IdentifierTypes::BROWSER_OBJECT_PARAMETER,
                'expectedValue' => '$browser.url',
                'expectedPosition' => 1,
            ],
        ];
    }

    /**
     * @dataProvider createReferencedElementDataProvider
     */
    public function testCreateWithElementReference(
        string $identifierString,
        array $existingIdentifiers,
        string $expectedType,
        string $expectedValue,
        int $expectedPosition,
        ?IdentifierInterface $expectedParentIdentifier
    ) {
        $identifier = $this->factory->createWithElementReference($identifierString, null, $existingIdentifiers);

        $this->assertInstanceOf(IdentifierInterface::class, $identifier);

        if ($identifier instanceof IdentifierInterface) {
            $this->assertSame($expectedType, $identifier->getType());
            $this->assertSame($expectedValue, $identifier->getValue());
            $this->assertSame($expectedPosition, $identifier->getPosition());
            $this->assertNull($identifier->getName());
            $this->assertEquals($expectedParentIdentifier, $identifier->getParentIdentifier());
        }
    }

    public function createReferencedElementDataProvider(): array
    {
        $parentIdentifier = new Identifier(
            IdentifierTypes::CSS_SELECTOR,
            '.parent',
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
                'expectedType' => IdentifierTypes::CSS_SELECTOR,
                'expectedValue' => '.selector',
                'expectedPosition' => 1,
                'expectedParentIdentifier' => null,
            ],
            'element reference with css selector, position null' => [
                'identifierString' => '"{{ element_name }} .selector"',
                'existingIdentifiers' => $existingIdentifiers,
                'expectedType' => IdentifierTypes::CSS_SELECTOR,
                'expectedValue' => '.selector',
                'expectedPosition' => 1,
                'expectedParentIdentifier' => $parentIdentifier,
            ],
            'element reference with css selector, position 1' => [
                'identifierString' => '"{{ element_name }} .selector":1',
                'existingIdentifiers' => $existingIdentifiers,
                'expectedType' => IdentifierTypes::CSS_SELECTOR,
                'expectedValue' => '.selector',
                'expectedPosition' => 1,
                'expectedParentIdentifier' => $parentIdentifier,
            ],
            'element reference with css selector, position 2' => [
                'identifierString' => '"{{ element_name }} .selector":2',
                'existingIdentifiers' => $existingIdentifiers,
                'expectedType' => IdentifierTypes::CSS_SELECTOR,
                'expectedValue' => '.selector',
                'expectedPosition' => 2,
                'expectedParentIdentifier' => $parentIdentifier,
            ],
            'invalid double element reference with css selector' => [
                'identifierString' => '"{{ element_name }} {{ another_element_name }} .selector"',
                'existingIdentifiers' => $existingIdentifiers,
                'expectedType' => IdentifierTypes::CSS_SELECTOR,
                'expectedValue' => '{{ another_element_name }} .selector',
                'expectedPosition' => 1,
                'expectedParentIdentifier' => $parentIdentifier,
            ],
            'element reference with xpath expression, position null' => [
                'identifierString' => '"{{ element_name }} //foo"',
                'existingIdentifiers' => $existingIdentifiers,
                'expectedType' => IdentifierTypes::XPATH_EXPRESSION,
                'expectedValue' => '//foo',
                'expectedPosition' => 1,
                'expectedParentIdentifier' => $parentIdentifier,
            ],
            'element reference with xpath expression, position 1' => [
                'identifierString' => '"{{ element_name }} //foo":1',
                'existingIdentifiers' => $existingIdentifiers,
                'expectedType' => IdentifierTypes::XPATH_EXPRESSION,
                'expectedValue' => '//foo',
                'expectedPosition' => 1,
                'expectedParentIdentifier' => $parentIdentifier,
            ],
            'element reference with xpath expression, position 2' => [
                'identifierString' => '"{{ element_name }} //foo":2',
                'existingIdentifiers' => $existingIdentifiers,
                'expectedType' => IdentifierTypes::XPATH_EXPRESSION,
                'expectedValue' => '//foo',
                'expectedPosition' => 2,
                'expectedParentIdentifier' => $parentIdentifier,
            ],
        ];
    }

    public function testCreateEmpty()
    {
        $this->assertNull($this->factory->create('', []));
        $this->assertNull($this->factory->create(' ', []));
    }

    public function testCreateWithElementReferenceEmpty()
    {
        $this->assertNull($this->factory->createWithElementReference('', null, []));
        $this->assertNull($this->factory->createWithElementReference(' ', null, []));
    }

    public function testCreateForInvalidPageElementReference()
    {
        $this->expectException(MalformedPageElementReferenceException::class);
        $this->expectExceptionMessage('Malformed page element reference "invalid-page-model-element-reference"');

        $this->factory->create('invalid-page-model-element-reference', []);
    }

    public function testCreateForPageElementReferenceForUnknownPage()
    {
        $this->expectException(UnknownPageException::class);
        $this->expectExceptionMessage('Unknown page "import_name"');

        $this->factory->create('import_name.elements.element_name', []);
    }

    public function testCreateForPageElementReferenceForUnknownElement()
    {
        $this->expectException(UnknownPageElementException::class);
        $this->expectExceptionMessage('Unknown page element "element_name" in page "import_name"');

        $this->factory->create(
            'import_name.elements.element_name',
            [
                'import_name' => new Page(new Uri('http://example.com'), [])
            ]
        );
    }
}
