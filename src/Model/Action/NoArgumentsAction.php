<?php

namespace webignition\BasilParser\Model\Action;

class NoArgumentsAction extends AbstractAction
{
    public function __construct(string $type)
    {
        parent::__construct($type, true);
    }
}
