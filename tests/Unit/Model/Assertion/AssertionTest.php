<?php

namespace webignition\BasilParser\Tests\Unit\Model\Assertion;

use webignition\BasilParser\Model\Assertion\Assertion;
use webignition\BasilParser\Model\Assertion\AssertionComparisons;
use webignition\BasilParser\Model\Value\Value;
use webignition\BasilParser\Model\Value\ValueTypes;
use webignition\BasilParser\Model\Identifier\Identifier;
use webignition\BasilParser\Model\Identifier\IdentifierTypes;

class AssertionTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $assertionString = '.foo is "foo"';
        $identifier = new Identifier(IdentifierTypes::CSS_SELECTOR, '.foo');
        $comparison = AssertionComparisons::IS;
        $value = new Value(ValueTypes::STRING, 'foo');

        $assertion = new Assertion($assertionString, $identifier, $comparison, $value);

        $this->assertSame($assertionString, $assertion->getAssertionString());
        $this->assertSame($identifier, $assertion->getIdentifier());
        $this->assertSame($comparison, $assertion->getComparison());
        $this->assertSame($value, $assertion->getValue());
    }
}
