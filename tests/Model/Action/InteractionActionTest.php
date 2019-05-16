<?php

namespace webignition\BasilParser\Tests\Model\Action;

use webignition\BasilParser\Model\Action\ActionTypesInterface;
use webignition\BasilParser\Model\Action\InteractionAction;
use webignition\BasilParser\Model\Identifier\Identifier;
use webignition\BasilParser\Model\Identifier\IdentifierTypesInterface;

class InteractionActionTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $verb = ActionTypesInterface::CLICK;
        $identifier = new Identifier(IdentifierTypesInterface::CSS_SELECTOR, '.foo');

        $action = new InteractionAction($verb, $identifier);

        $this->assertSame($verb, $action->getVerb());
        $this->assertSame($identifier, $action->getIdentifier());
    }
}
