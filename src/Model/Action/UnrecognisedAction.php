<?php

namespace webignition\BasilParser\Model\Action;

class UnrecognisedAction extends AbstractAction
{
    public function __construct(string $type, string $arguments)
    {
        parent::__construct($type, $arguments, false);
    }
}
