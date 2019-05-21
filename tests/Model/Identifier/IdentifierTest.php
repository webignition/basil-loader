<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Model\Identifier;

use webignition\BasilParser\Model\Identifier\Identifier;
use webignition\BasilParser\Model\Identifier\IdentifierInterface;
use webignition\BasilParser\Model\Identifier\IdentifierTypes;

class IdentifierTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(string $type, string $value, int $expectedPosition, ?int $position = null)
    {
        $identifier = new Identifier($type, $value, $position);

        $this->assertSame($type, $identifier->getType());
        $this->assertSame($value, $identifier->getValue());
        $this->assertSame($expectedPosition, $identifier->getPosition());
    }

    public function createDataProvider(): array
    {
        return [
            'no explicit position' => [
                'type' => IdentifierTypes::CSS_SELECTOR,
                'value' => '.foo',
                'expectedPosition' => Identifier::DEFAULT_POSITION,
            ],
            'has explicit position' => [
                'type' => IdentifierTypes::CSS_SELECTOR,
                'value' => '.foo',
                'expectedPosition' => 3,
                'position' => 3,
            ],
        ];
    }

    public function testWithElementReference()
    {
        $identifier = new Identifier(IdentifierTypes::CSS_SELECTOR, '.selector');

        $this->assertNull($identifier->getElementReference());

        $identifierWithElementReference = $identifier->withElementReference('element_name');

        $this->assertNotSame($identifier, $identifierWithElementReference);
        $this->assertEquals('element_name', $identifierWithElementReference->getElementReference());
    }

    /**
     * @dataProvider toStringDataProvider
     */
    public function testToString(IdentifierInterface $identifier, string $expectedString)
    {
        $this->assertSame($expectedString, (string) $identifier);
    }

    public function toStringDataProvider(): array
    {
        $cssSelectorWithElementReference = new Identifier(
            IdentifierTypes::CSS_SELECTOR,
            '.selector'
        );

        $cssSelectorWithElementReference =
            $cssSelectorWithElementReference->withElementReference('referenced_element_name');

        $xpathExpressionWithElementReference = new Identifier(
            IdentifierTypes::XPATH_EXPRESSION,
            '//foo'
        );

        $xpathExpressionWithElementReference =
            $xpathExpressionWithElementReference->withElementReference('referenced_element_name');

        return [
            'css selector, position null' => [
                'identifier' => new Identifier(IdentifierTypes::CSS_SELECTOR, '.selector'),
                'expectedString' => '".selector"',
            ],
            'css selector, position 1' => [
                'identifier' => new Identifier(IdentifierTypes::CSS_SELECTOR, '.selector', 1),
                'expectedString' => '".selector"',
            ],
            'css selector, position 2' => [
                'identifier' => new Identifier(IdentifierTypes::CSS_SELECTOR, '.selector', 2),
                'expectedString' => '".selector":2',
            ],
            'xpath expression, position null' => [
                'identifier' => new Identifier(IdentifierTypes::XPATH_EXPRESSION, '//foo'),
                'expectedString' => '"//foo"',
            ],
            'xpath expression, position 1' => [
                'identifier' => new Identifier(IdentifierTypes::XPATH_EXPRESSION, '//foo', 1),
                'expectedString' => '"//foo"',
            ],
            'xpath expression, position 2' => [
                'identifier' => new Identifier(IdentifierTypes::XPATH_EXPRESSION, '//foo', 2),
                'expectedString' => '"//foo":2',
            ],
            'page model element reference, position null' => [
                'identifier' => new Identifier(
                    IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                    'page_model.elements.element_name'
                ),
                'expectedString' => 'page_model.elements.element_name',
            ],
            'page model element reference, position 1' => [
                'identifier' => new Identifier(
                    IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                    'page_model.elements.element_name',
                    1
                ),
                'expectedString' => 'page_model.elements.element_name',
            ],
            'page model element reference, position 2' => [
                'identifier' => new Identifier(
                    IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                    'page_model.elements.element_name',
                    2
                ),
                'expectedString' => 'page_model.elements.element_name:2',
            ],
            'element parameter, position null' => [
                'identifier' => new Identifier(
                    IdentifierTypes::ELEMENT_PARAMETER,
                    '$elements.element_name'
                ),
                'expectedString' => '$elements.element_name',
            ],
            'element parameter, position 1' => [
                'identifier' => new Identifier(
                    IdentifierTypes::ELEMENT_PARAMETER,
                    '$elements.element_name',
                    1
                ),
                'expectedString' => '$elements.element_name',
            ],
            'element parameter, position 2' => [
                'identifier' => new Identifier(
                    IdentifierTypes::ELEMENT_PARAMETER,
                    '$elements.element_name',
                    2
                ),
                'expectedString' => '$elements.element_name:2',
            ],
            'css selector with element reference, position null' => [
                'identifier' => $cssSelectorWithElementReference,
                'expectedString' => '"{{ referenced_element_name }} .selector"',
            ],
            'xpath expression with element reference, position null' => [
                'identifier' => $xpathExpressionWithElementReference,
                'expectedString' => '"{{ referenced_element_name }} //foo"',
            ],
        ];
    }
}
