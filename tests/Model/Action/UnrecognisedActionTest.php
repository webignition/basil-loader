<?php

namespace webignition\BasilParser\Tests\Model\Action;

use webignition\BasilParser\Model\Action\ActionTypes;
use webignition\BasilParser\Model\Action\UnrecognisedAction;

class UnrecognisedActionTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $type = ActionTypes::RELOAD;

        $action = new UnrecognisedAction($type);

        $this->assertSame($type, $action->getType());
        $this->assertFalse($action->isRecognised());
    }
}
