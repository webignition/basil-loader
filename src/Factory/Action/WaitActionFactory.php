<?php

namespace webignition\BasilParser\Factory\Action;

use webignition\BasilParser\Model\Action\ActionInterface;
use webignition\BasilParser\Model\Action\ActionTypes;
use webignition\BasilParser\Model\Action\WaitAction;
use webignition\BasilParser\PageProvider\PageProviderInterface;

class WaitActionFactory extends AbstractActionFactory implements ActionFactoryInterface
{
    protected function getHandledActionTypes(): array
    {
        return [
            ActionTypes::WAIT,
        ];
    }

    protected function doCreateFromTypeAndArguments(
        string $type,
        string $arguments,
        PageProviderInterface $pageProvider
    ): ActionInterface {
        return new WaitAction($arguments);
    }
}
