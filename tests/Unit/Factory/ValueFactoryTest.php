<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Factory;

use webignition\BasilParser\Factory\ValueFactory;
use webignition\BasilParser\Model\Value\ValueInterface;
use webignition\BasilParser\Model\Value\ValueTypes;

class ValueFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ValueFactory
     */
    private $valueFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->valueFactory = new ValueFactory();
    }

    /**
     * @dataProvider createFromValueStringDataProvider
     */
    public function testCreateFromValueString(string $valueString, string $expectedType, string $expectedValue)
    {
        $value = $this->valueFactory->createFromValueString($valueString);

        $this->assertInstanceOf(ValueInterface::class, $value);
        $this->assertEquals($expectedType, $value->getType());
        $this->assertEquals($expectedValue, $value->getValue());
    }

    public function createFromValueStringDataProvider(): array
    {
        return [
            'quoted string' => [
                'valueString' => '"value"',
                'expectedType' => ValueTypes::STRING,
                'expectedValue' => 'value',
            ],
            'unquoted string' => [
                'valueString' => 'value',
                'expectedType' => ValueTypes::STRING,
                'expectedValue' => 'value',
            ],
            'quoted string wrapped with escaped quotes' => [
                'valueString' => '"\"value\""',
                'expectedType' => ValueTypes::STRING,
                'expectedValue' => '"value"',
            ],
            'quoted string containing escaped quotes' => [
                'valueString' => '"v\"alu\"e"',
                'expectedType' => ValueTypes::STRING,
                'expectedValue' => 'v"alu"e',
            ],
            'data parameter' => [
                'valueString' => '$data.name',
                'expectedType' => ValueTypes::DATA_PARAMETER,
                'expectedValue' => '$data.name',
            ],
        ];
    }
}