<?php

namespace webignition\BasilParser\Model\Value;

interface ValueInterface
{
    public function getType(): string;
    public function getValue(): string;
}