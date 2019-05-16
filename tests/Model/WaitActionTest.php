<?php

namespace webignition\BasilParser\Tests\Model;

use webignition\BasilParser\Model\ActionTypesInterface;
use webignition\BasilParser\Model\WaitAction;

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
