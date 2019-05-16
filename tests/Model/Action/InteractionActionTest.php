<?php

namespace webignition\BasilParser\Tests\Model\Action;

use webignition\BasilParser\Model\Action\TypesInterface;
use webignition\BasilParser\Model\Action\InteractionAction;

class InteractionActionTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $verb = TypesInterface::CLICK;
        $identifier = '.class';

        $action = new InteractionAction($verb, $identifier);

        $this->assertSame($verb, $action->getVerb());
        $this->assertSame($identifier, $action->getIdentifier());
    }
}
