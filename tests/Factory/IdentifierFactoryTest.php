<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Factory;

use webignition\BasilParser\Factory\IdentifierFactory;
use webignition\BasilParser\Model\Identifier\IdentifierInterface;
use webignition\BasilParser\Model\Identifier\IdentifierTypes;

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
     * @dataProvider createReferencedElementDataProvider
     */
    public function testCreateNonEmpty(
        string $identifierString,
        string $expectedType,
        string $expectedValue,
        ?string $expectedElementReference,
        int $expectedPosition
    ) {
        $identifier = $this->factory->create($identifierString);

        $this->assertInstanceOf(IdentifierInterface::class, $identifier);

        if ($identifier instanceof IdentifierInterface) {
            $this->assertSame($expectedType, $identifier->getType());
            $this->assertSame($expectedValue, $identifier->getValue());
            $this->assertSame($expectedElementReference, $identifier->getElementReference());
            $this->assertSame($expectedPosition, $identifier->getPosition());
        }
    }

    public function createCssSelectorDataProvider(): array
    {
        return [
            'css id selector' => [
                'identifierString' => '"#element-id"',
                'expectedType' => IdentifierTypes::CSS_SELECTOR,
                'expectedValue' => '#element-id',
                'expectedElementReference' => null,
                'expectedPosition' => 1,
            ],
            'css class selector, position: null' => [
                'identifierString' => '".listed-item"',
                'expectedType' => IdentifierTypes::CSS_SELECTOR,
                'expectedValue' => '.listed-item',
                'expectedElementReference' => null,
                'expectedPosition' => 1,
            ],
            'css class selector; position: 1' => [
                'identifierString' => '".listed-item":1',
                'expectedType' => IdentifierTypes::CSS_SELECTOR,
                'expectedValue' => '.listed-item',
                'expectedElementReference' => null,
                'expectedPosition' => 1,
            ],
            'css class selector; position: 3' => [
                'identifierString' => '".listed-item":3',
                'expectedType' => IdentifierTypes::CSS_SELECTOR,
                'expectedValue' => '.listed-item',
                'expectedElementReference' => null,
                'expectedPosition' => 3,
            ],
            'css class selector; position: -1' => [
                'identifierString' => '".listed-item":-1',
                'expectedType' => IdentifierTypes::CSS_SELECTOR,
                'expectedValue' => '.listed-item',
                'expectedElementReference' => null,
                'expectedPosition' => -1,
            ],
            'css class selector; position: -3' => [
                'identifierString' => '".listed-item":-3',
                'expectedType' => IdentifierTypes::CSS_SELECTOR,
                'expectedValue' => '.listed-item',
                'expectedElementReference' => null,
                'expectedPosition' => -3,
            ],
            'css class selector; position: first' => [
                'identifierString' => '".listed-item":first',
                'expectedType' => IdentifierTypes::CSS_SELECTOR,
                'expectedValue' => '.listed-item',
                'expectedElementReference' => null,
                'expectedPosition' => 1,
            ],
            'css class selector; position: last' => [
                'identifierString' => '".listed-item":last',
                'expectedType' => IdentifierTypes::CSS_SELECTOR,
                'expectedValue' => '.listed-item',
                'expectedElementReference' => null,
                'expectedPosition' => -1,
            ],
        ];
    }

    public function createXpathExpressionDataProvider(): array
    {
        return [
            'xpath id selector' => [
                'identifierString' => '"//*[@id="element-id"]"',
                'expectedType' => IdentifierTypes::XPATH_EXPRESSION,
                'expectedValue' => '//*[@id="element-id"]',
                'expectedElementReference' => null,
                'expectedPosition' => 1,
            ],
            'xpath attribute selector, position: null' => [
                'identifierString' => '"//input[@type="submit"]"',
                'expectedType' => IdentifierTypes::XPATH_EXPRESSION,
                'expectedValue' => '//input[@type="submit"]',
                'expectedElementReference' => null,
                'expectedPosition' => 1,
            ],
            'xpath attribute selector; position: 1' => [
                'identifierString' => '"//input[@type="submit"]":1',
                'expectedType' => IdentifierTypes::XPATH_EXPRESSION,
                'expectedValue' => '//input[@type="submit"]',
                'expectedElementReference' => null,
                'expectedPosition' => 1,
            ],
            'xpath attribute selector; position: 3' => [
                'identifierString' => '"//input[@type="submit"]":3',
                'expectedType' => IdentifierTypes::XPATH_EXPRESSION,
                'expectedValue' => '//input[@type="submit"]',
                'expectedElementReference' => null,
                'expectedPosition' => 3,
            ],
            'xpath attribute selector; position: -1' => [
                'identifierString' => '"//input[@type="submit"]":-1',
                'expectedType' => IdentifierTypes::XPATH_EXPRESSION,
                'expectedValue' => '//input[@type="submit"]',
                'expectedElementReference' => null,
                'expectedPosition' => -1,
            ],
            'xpath attribute selector; position: -3' => [
                'identifierString' => '"//input[@type="submit"]":-3',
                'expectedType' => IdentifierTypes::XPATH_EXPRESSION,
                'expectedValue' => '//input[@type="submit"]',
                'expectedElementReference' => null,
                'expectedPosition' => -3,
            ],
            'xpath attribute selector; position: first' => [
                'identifierString' => '"//input[@type="submit"]":first',
                'expectedType' => IdentifierTypes::XPATH_EXPRESSION,
                'expectedValue' => '//input[@type="submit"]',
                'expectedElementReference' => null,
                'expectedPosition' => 1,
            ],
            'xpath attribute selector; position: last' => [
                'identifierString' => '"//input[@type="submit"]":last',
                'expectedType' => IdentifierTypes::XPATH_EXPRESSION,
                'expectedValue' => '//input[@type="submit"]',
                'expectedElementReference' => null,
                'expectedPosition' => -1,
            ],
        ];
    }

    public function createElementParameterDataProvider(): array
    {
        return [
            'element parameter' => [
                'identifierString' => '$element.name',
                'expectedType' => IdentifierTypes::ELEMENT_PARAMETER,
                'expectedValue' => '$element.name',
                'expectedElementReference' => null,
                'expectedPosition' => 1,
            ],
        ];
    }

    public function createPageModelElementReferenceDataProvider(): array
    {
        return [
            'element parameter' => [
                'identifierString' => 'page_import_name.elements.element_name',
                'expectedType' => IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                'expectedValue' => 'page_import_name.elements.element_name',
                'expectedElementReference' => null,
                'expectedPosition' => 1,
            ],
        ];
    }

    public function createReferencedElementDataProvider(): array
    {
        return [
            'element reference with css selector, position null' => [
                'identifierString' => '"{{ element_name }} .selector"',
                'expectedType' => IdentifierTypes::CSS_SELECTOR,
                'expectedValue' => '.selector',
                'expectedElementReference' => 'element_name',
                'expectedPosition' => 1,
            ],
            'element reference with css selector, position 1' => [
                'identifierString' => '"{{ element_name }} .selector":1',
                'expectedType' => IdentifierTypes::CSS_SELECTOR,
                'expectedValue' => '.selector',
                'expectedElementReference' => 'element_name',
                'expectedPosition' => 1,
            ],
            'element reference with css selector, position 2' => [
                'identifierString' => '"{{ element_name }} .selector":2',
                'expectedType' => IdentifierTypes::CSS_SELECTOR,
                'expectedValue' => '.selector',
                'expectedElementReference' => 'element_name',
                'expectedPosition' => 2,
            ],
            'double element reference with css selector' => [
                'identifierString' => '"{{ first_element_name }} {{ second_element_name }} .selector"',
                'expectedType' => IdentifierTypes::CSS_SELECTOR,
                'expectedValue' => '{{ second_element_name }} .selector',
                'expectedElementReference' => 'first_element_name',
                'expectedPosition' => 1,
            ],
            'element reference with xpath expression, position null' => [
                'identifierString' => '"{{ element_name }} //foo"',
                'expectedType' => IdentifierTypes::XPATH_EXPRESSION,
                'expectedValue' => '//foo',
                'expectedElementReference' => 'element_name',
                'expectedPosition' => 1,
            ],
            'element reference with xpath expression, position 1' => [
                'identifierString' => '"{{ element_name }} //foo":1',
                'expectedType' => IdentifierTypes::XPATH_EXPRESSION,
                'expectedValue' => '//foo',
                'expectedElementReference' => 'element_name',
                'expectedPosition' => 1,
            ],
            'element reference with xpath expression, position 2' => [
                'identifierString' => '"{{ element_name }} //foo":2',
                'expectedType' => IdentifierTypes::XPATH_EXPRESSION,
                'expectedValue' => '//foo',
                'expectedElementReference' => 'element_name',
                'expectedPosition' => 2,
            ],
        ];
    }

    public function testCreateEmpty()
    {
        $this->assertNull($this->factory->create(''));
        $this->assertNull($this->factory->create(' '));
    }
}
