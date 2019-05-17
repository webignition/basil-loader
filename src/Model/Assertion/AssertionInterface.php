<?php

namespace webignition\BasilParser\Model\Assertion;

use webignition\BasilParser\Model\Identifier\IdentifierInterface;
use webignition\BasilParser\Model\Value\ValueInterface;

interface AssertionInterface
{
    public function getIdentifier(): IdentifierInterface;
    public function getComparison(): string;
    public function getValue(): ?ValueInterface;
}
