<?php

namespace webignition\BasilParser\Tests\Model\Action;

use webignition\BasilParser\Model\Action\ActionTypesInterface;
use webignition\BasilParser\Model\Action\WaitAction;

class WaitActionTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $numberOfSeconds = 10;

        $action = new WaitAction($numberOfSeconds);

        $this->assertSame(ActionTypesInterface::WAIT, $action->getVerb());
        $this->assertSame($numberOfSeconds, $action->getNumberOfSeconds());
    }
}
