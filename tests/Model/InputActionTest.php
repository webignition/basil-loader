<?php

namespace webignition\BasilParser\Tests\Model;

use webignition\BasilParser\Model\ActionTypesInterface;
use webignition\BasilParser\Model\InputAction;

class InputActionTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $identifier = '.class';
        $value = 'foo';

        $action = new InputAction($identifier, $value);

        $this->assertSame(ActionTypesInterface::SET, $action->getVerb());
        $this->assertSame($identifier, $action->getIdentifier());
        $this->assertSame($value, $action->getValue());
    }
}
