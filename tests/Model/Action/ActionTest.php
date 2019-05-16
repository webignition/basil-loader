<?php

namespace webignition\BasilParser\Tests\Model\Action;

use webignition\BasilParser\Model\Action\Action;
use webignition\BasilParser\Model\Action\ActionTypesInterface;

class ActionTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $verb = ActionTypesInterface::RELOAD;

        $action = new Action($verb);

        $this->assertSame($verb, $action->getVerb());
    }
}
