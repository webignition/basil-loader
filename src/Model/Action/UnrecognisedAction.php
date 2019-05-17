<?php

namespace webignition\BasilParser\Model\Action;

class UnrecognisedAction extends AbstractAction
{
    public function __construct(string $type)
    {
        parent::__construct($type, false);
    }
}
