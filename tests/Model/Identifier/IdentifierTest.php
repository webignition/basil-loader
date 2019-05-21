<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Model\Identifier;

use webignition\BasilParser\Model\Identifier\Identifier;
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

    /**
     * @dataProvider toStringDataProvider
     */
    public function testToString(string $type, string $value, ?int $position, string $expectedString)
    {
        $identifier = new Identifier($type, $value, $position);

        $this->assertSame($expectedString, (string) $identifier);
    }

    public function toStringDataProvider(): array
    {
        return [
            'css selector, position null' => [
                'type' => IdentifierTypes::CSS_SELECTOR,
                'value' => '.selector',
                'position' => null,
                'expectedString' => '".selector"',
            ],
            'css selector, position 1' => [
                'type' => IdentifierTypes::CSS_SELECTOR,
                'value' => '.selector',
                'position' => 1,
                'expectedString' => '".selector"',
            ],
            'css selector, position 2' => [
                'type' => IdentifierTypes::CSS_SELECTOR,
                'value' => '.selector',
                'position' => 2,
                'expectedString' => '".selector":2',
            ],
            'xpath expression, position null' => [
                'type' => IdentifierTypes::XPATH_EXPRESSION,
                'value' => '//foo',
                'position' => null,
                'expectedString' => '"//foo"',
            ],
            'xpath expression, position 1' => [
                'type' => IdentifierTypes::XPATH_EXPRESSION,
                'value' => '//foo',
                'position' => 1,
                'expectedString' => '"//foo"',
            ],
            'xpath expression, position 2' => [
                'type' => IdentifierTypes::XPATH_EXPRESSION,
                'value' => '//foo',
                'position' => 2,
                'expectedString' => '"//foo":2',
            ],
            'page model element reference, position null' => [
                'type' => IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                'value' => 'page_model.elements.element_name',
                'position' => null,
                'expectedString' => 'page_model.elements.element_name',
            ],
            'page model element reference, position 1' => [
                'type' => IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                'value' => 'page_model.elements.element_name',
                'position' => 1,
                'expectedString' => 'page_model.elements.element_name',
            ],
            'page model element reference, position 2' => [
                'type' => IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE,
                'value' => 'page_model.elements.element_name',
                'position' => 2,
                'expectedString' => 'page_model.elements.element_name:2',
            ],
            'element parameter, position null' => [
                'type' => IdentifierTypes::ELEMENT_PARAMETER,
                'value' => '$elements.element_name',
                'position' => null,
                'expectedString' => '$elements.element_name',
            ],
            'element parameter, position 1' => [
                'type' => IdentifierTypes::ELEMENT_PARAMETER,
                'value' => '$elements.element_name',
                'position' => 1,
                'expectedString' => '$elements.element_name',
            ],
            'element parameter, position 2' => [
                'type' => IdentifierTypes::ELEMENT_PARAMETER,
                'value' => '$elements.element_name',
                'position' => 2,
                'expectedString' => '$elements.element_name:2',
            ],
        ];
    }
}
