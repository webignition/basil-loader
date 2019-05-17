<?php

namespace webignition\BasilParser\Model\Action;

class Action implements ActionInterface
{
    private $type = '';

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
