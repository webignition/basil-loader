<?php

namespace webignition\BasilParser\Model\Action;

class Action implements ActionInterface
{
    private $verb = '';

    public function __construct(string $verb)
    {
        $this->verb = $verb;
    }

    public function getVerb(): string
    {
        return $this->verb;
    }
}
