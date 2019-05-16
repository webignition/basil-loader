<?php

namespace webignition\BasilParser\Model\Assertion;

use webignition\BasilParser\Model\Identifier\IdentifierInterface;

interface AssertionInterface
{
    public function getIdentifier(): IdentifierInterface;
    public function getComparison(): string;
    public function getValue(): ?AssertionValueInterface;
}
