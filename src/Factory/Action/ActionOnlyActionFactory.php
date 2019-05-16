<?php

namespace webignition\BasilParser\Factory\Action;

use webignition\BasilParser\Model\Action\Action;
use webignition\BasilParser\Model\Action\ActionInterface;
use webignition\BasilParser\Model\Action\ActionTypes;

class ActionOnlyActionFactory extends AbstractActionFactory implements ActionFactoryInterface
{
    protected function getHandledActionTypes(): array
    {
        return [
            ActionTypes::RELOAD,
            ActionTypes::BACK,
            ActionTypes::FORWARD,
        ];
    }

    protected function doCreateFromTypeAndArguments(string $type, string $arguments): ActionInterface
    {
        return new Action($type);
    }
}
