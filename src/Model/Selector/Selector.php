<?php

namespace webignition\BasilParser\Model\Selector;

class Selector implements SelectorInterface
{
    private $type = '';
    private $value = '';

    public function __construct(string $type, string $value)
    {
        $this->type = $type;
        $this->value = $value;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
