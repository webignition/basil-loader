<?php

namespace webignition\BasilParser\Tests\Model\Identifier;

use webignition\BasilParser\Model\Identifier\Identifier;
use webignition\BasilParser\Model\Identifier\TypesInterface;

class IdentifierTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $type = TypesInterface::SELECTOR;
        $value = '.foo';

        $identifier = new Identifier($type, $value);

        $this->assertSame($type, $identifier->getType());
        $this->assertSame($value, $identifier->getValue());
    }
}
