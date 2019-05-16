<?php

namespace webignition\BasilParser\Model\Identifier;

interface IdentifierInterface
{
    public function getType(): string;
    public function getValue(): string;
}
