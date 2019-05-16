<?php

namespace webignition\BasilParser\Model\Assertion;

interface AssertionValueInterface
{
    public function getType(): string;
    public function getValue(): string;
}
