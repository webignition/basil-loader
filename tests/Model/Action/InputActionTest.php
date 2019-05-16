<?php

namespace webignition\BasilParser\Tests\Model\Action;

use webignition\BasilParser\Model\Action\ActionTypesInterface;
use webignition\BasilParser\Model\Action\InputAction;
use webignition\BasilParser\Model\Identifier\Identifier;
use webignition\BasilParser\Model\Identifier\IdentifierTypesInterface;

class InputActionTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $identifier = new Identifier(IdentifierTypesInterface::SELECTOR, '.foo');
        $value = 'foo';

        $action = new InputAction($identifier, $value);

        $this->assertSame(ActionTypesInterface::SET, $action->getVerb());
        $this->assertSame($identifier, $action->getIdentifier());
        $this->assertSame($value, $action->getValue());
    }
}
