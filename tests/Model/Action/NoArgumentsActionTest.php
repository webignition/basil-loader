<?php

namespace webignition\BasilParser\Tests\Model\Action;

use webignition\BasilParser\Model\Action\ActionTypes;
use webignition\BasilParser\Model\Action\NoArgumentsAction;

class NoArgumentsActionTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $type = 'reload';

        $action = new NoArgumentsAction($type);

        $this->assertSame(ActionTypes::RELOAD, $action->getType());
        $this->assertTrue($action->isRecognised());
    }
}
