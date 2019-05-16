<?php

namespace webignition\BasilParser\Tests\Model\Selector;

use webignition\BasilParser\Model\Selector\Selector;
use webignition\BasilParser\Model\Selector\SelectorTypesInterface;

class SelectorTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $type = SelectorTypesInterface::CSS;
        $value = '.foo';

        $selector = new Selector($type, $value);

        $this->assertSame($type, $selector->getType());
        $this->assertSame($value, $selector->getValue());
    }
}
