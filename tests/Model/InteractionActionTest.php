<?php

namespace webignition\BasilParser\Tests\Model;

use webignition\BasilParser\Model\ActionTypesInterface;
use webignition\BasilParser\Model\InteractionAction;

class InteractionActionTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $verb = ActionTypesInterface::CLICK;
        $identifier = '.class';

        $action = new InteractionAction($verb, $identifier);

        $this->assertSame($verb, $action->getVerb());
        $this->assertSame($identifier, $action->getIdentifier());
    }
}
