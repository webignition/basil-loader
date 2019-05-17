<?php

namespace webignition\BasilParser\Model\Action;

class NoArgumentsAction extends AbstractAction
{
    public function __construct(string $type, string $arguments)
    {
        parent::__construct($type, $arguments, true);
    }
}
