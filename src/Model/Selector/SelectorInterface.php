<?php

namespace webignition\BasilParser\Model\Selector;

interface SelectorInterface
{
    public function getType(): string;
    public function getValue(): string;
}
