<?php

namespace webignition\BasilParser\Tests\Model;

use webignition\BasilParser\Model\Action;
use webignition\BasilParser\Model\ActionTypesInterface;

class ActionTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $verb = ActionTypesInterface::RELOAD;

        $action = new Action($verb);

        $this->assertSame($verb, $action->getVerb());
    }
}
