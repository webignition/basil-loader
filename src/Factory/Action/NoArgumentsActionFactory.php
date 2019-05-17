<?php

namespace webignition\BasilParser\Factory\Action;

use webignition\BasilParser\Model\Action\NoArgumentsAction;
use webignition\BasilParser\Model\Action\ActionInterface;
use webignition\BasilParser\Model\Action\ActionTypes;

class NoArgumentsActionFactory extends AbstractActionFactory implements ActionFactoryInterface
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
        return new NoArgumentsAction($type);
    }
}
