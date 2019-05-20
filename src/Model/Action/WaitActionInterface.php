<?php

namespace webignition\BasilParser\Model\Action;

interface WaitActionInterface extends ActionInterface
{
    public function getDuration(): string;
}
