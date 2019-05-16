<?php

namespace webignition\BasilParser\Tests\Model\Action;

use webignition\BasilParser\Model\Action\TypesInterface;
use webignition\BasilParser\Model\Action\InputAction;

class InputActionTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $identifier = '.class';
        $value = 'foo';

        $action = new InputAction($identifier, $value);

        $this->assertSame(TypesInterface::SET, $action->getVerb());
        $this->assertSame($identifier, $action->getIdentifier());
        $this->assertSame($value, $action->getValue());
    }
}
