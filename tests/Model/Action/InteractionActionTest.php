<?php

namespace webignition\BasilParser\Tests\Model\Action;

use webignition\BasilParser\Model\Action\ActionTypes;
use webignition\BasilParser\Model\Action\InteractionAction;
use webignition\BasilParser\Model\Identifier\Identifier;
use webignition\BasilParser\Model\Identifier\IdentifierTypes;

class InteractionActionTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $type = ActionTypes::CLICK;
        $identifier = new Identifier(IdentifierTypes::CSS_SELECTOR, '.foo');

        $action = new InteractionAction($type, $identifier);

        $this->assertSame($type, $action->getType());
        $this->assertSame($identifier, $action->getIdentifier());
        $this->assertTrue($action->isRecognised());
    }
}
