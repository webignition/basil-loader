<?php

namespace webignition\BasilParser\Factory\Action;

use webignition\BasilModel\Action\ActionInterface;
use webignition\BasilModel\Action\ActionTypes;
use webignition\BasilModel\Action\WaitAction;
use webignition\BasilParser\Provider\Page\PageProviderInterface;

class WaitActionTypeFactory extends AbstractActionTypeFactory implements ActionTypeFactoryInterface
{
    protected function getHandledActionTypes(): array
    {
        return [
            ActionTypes::WAIT,
        ];
    }

    protected function doCreateForActionType(
        string $type,
        string $arguments,
        PageProviderInterface $pageProvider
    ): ActionInterface {
        return new WaitAction($arguments);
    }
}
