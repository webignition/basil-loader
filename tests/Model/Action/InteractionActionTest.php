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
        $verb = ActionTypes::CLICK;
        $identifier = new Identifier(IdentifierTypes::CSS_SELECTOR, '.foo');

        $action = new InteractionAction($verb, $identifier);

        $this->assertSame($verb, $action->getVerb());
        $this->assertSame($identifier, $action->getIdentifier());
    }
}
