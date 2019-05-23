<?php

namespace webignition\BasilParser\Tests\Unit\Model\Action;

use webignition\BasilParser\Model\Action\ActionTypes;
use webignition\BasilParser\Model\Action\WaitAction;

class WaitActionTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $duration = '10';

        $action = new WaitAction($duration);

        $this->assertSame(ActionTypes::WAIT, $action->getType());
        $this->assertSame('10', $action->getArguments());
        $this->assertSame($duration, $action->getDuration());
        $this->assertTrue($action->isRecognised());
    }
}
