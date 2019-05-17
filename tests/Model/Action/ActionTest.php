<?php

namespace webignition\BasilParser\Tests\Model\Action;

use webignition\BasilParser\Model\Action\Action;
use webignition\BasilParser\Model\Action\ActionTypes;

class ActionTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $type = ActionTypes::RELOAD;

        $action = new Action($type);

        $this->assertSame($type, $action->getType());
    }
}
