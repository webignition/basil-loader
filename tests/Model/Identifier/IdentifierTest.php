<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Model\Identifier;

use webignition\BasilParser\Model\Identifier\Identifier;
use webignition\BasilParser\Model\Identifier\IdentifierTypesInterface;

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
                'type' => IdentifierTypesInterface::CSS_SELECTOR,
                'value' => '.foo',
                'expectedPosition' => Identifier::DEFAULT_POSITION,
            ],
            'has explicit position' => [
                'type' => IdentifierTypesInterface::CSS_SELECTOR,
                'value' => '.foo',
                'expectedPosition' => 3,
                'position' => 3,
            ],
        ];
    }
}
