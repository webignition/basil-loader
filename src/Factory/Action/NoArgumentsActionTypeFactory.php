<?php

namespace webignition\BasilParser\Factory\Action;

use webignition\BasilModel\Action\NoArgumentsAction;
use webignition\BasilModel\Action\ActionInterface;
use webignition\BasilModel\Action\ActionTypes;

class NoArgumentsActionTypeFactory extends AbstractActionTypeFactory implements ActionTypeFactoryInterface
{
    protected function getHandledActionTypes(): array
    {
        return [
            ActionTypes::RELOAD,
            ActionTypes::BACK,
            ActionTypes::FORWARD,
        ];
    }

    protected function doCreateForActionType(string $type, string $arguments): ActionInterface
    {
        return new NoArgumentsAction($type, $arguments);
    }
}
