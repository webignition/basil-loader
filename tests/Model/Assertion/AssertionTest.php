<?php

namespace webignition\BasilParser\Tests\Model\Assertion;

use webignition\BasilParser\Model\Assertion\Assertion;
use webignition\BasilParser\Model\Assertion\AssertionComparisons;
use webignition\BasilParser\Model\Identifier\Identifier;
use webignition\BasilParser\Model\Identifier\IdentifierTypes;

class AssertionTest extends \PHPUnit\Framework\TestCase
{
    public function testCreate()
    {
        $identifier = new Identifier(
            IdentifierTypes::CSS_SELECTOR,
            '.foo'
        );
        $comparison = AssertionComparisons::IS;
        $value = 'foo';

        $assertion = new Assertion($identifier, $comparison, $value);

        $this->assertSame($identifier, $assertion->getIdentifier());
        $this->assertSame($comparison, $assertion->getComparison());
        $this->assertSame($value, $assertion->getValue());
    }
}
