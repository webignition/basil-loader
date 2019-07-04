<?php

namespace webignition\BasilParser\Factory\Action;

use webignition\BasilModel\Action\ActionInterface;
use webignition\BasilModel\Action\ActionTypes;
use webignition\BasilModel\Action\WaitAction;

class WaitActionTypeFactory extends AbstractActionTypeFactory implements ActionTypeFactoryInterface
{
    protected function getHandledActionTypes(): array
    {
        return [
            ActionTypes::WAIT,
        ];
    }

    protected function doCreateForActionType(string $type, string $arguments): ActionInterface
    {
        return new WaitAction($arguments);
    }
}
