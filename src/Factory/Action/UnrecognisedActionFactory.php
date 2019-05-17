<?php

namespace webignition\BasilParser\Factory\Action;

use webignition\BasilParser\Model\Action\ActionInterface;
use webignition\BasilParser\Model\Action\UnrecognisedAction;

class UnrecognisedActionFactory implements ActionFactoryInterface
{

    public function handles(string $type): bool
    {
        return true;
    }

    public function createFromActionString(string $actionString): ActionInterface
    {
        list($type, $arguments) = explode(' ', $actionString, 2);

        return $this->createFromTypeAndArguments($type, $arguments);
    }

    public function createFromTypeAndArguments(string $type, string $arguments): ActionInterface
    {
        return new UnrecognisedAction($type);
    }
}
