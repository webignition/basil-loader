<?php

namespace webignition\BasilParser\Tests\Unit\Model\Action;

use webignition\BasilParser\Model\Action\ActionTypes;
use webignition\BasilParser\Model\Action\InputAction;
use webignition\BasilParser\Model\Identifier\Identifier;
use webignition\BasilParser\Model\Identifier\IdentifierTypes;
use webignition\BasilParser\Model\Value\Value;
use webignition\BasilParser\Model\Value\ValueTypes;

class InputActionTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $identifier = new Identifier(IdentifierTypes::CSS_SELECTOR, '.foo');
        $value = new Value(ValueTypes::STRING, 'foo');

        $action = new InputAction($identifier, $value, '".foo" to "foo"');

        $this->assertSame(ActionTypes::SET, $action->getType());
        $this->assertSame('".foo" to "foo"', $action->getArguments());
        $this->assertSame($identifier, $action->getIdentifier());
        $this->assertSame($value, $action->getValue());
        $this->assertTrue($action->isRecognised());
    }
}