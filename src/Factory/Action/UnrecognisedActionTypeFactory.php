<?php

namespace webignition\BasilParser\Factory\Action;

use webignition\BasilParser\Model\Action\ActionInterface;
use webignition\BasilParser\Model\Action\UnrecognisedAction;
use webignition\BasilParser\PageProvider\PageProviderInterface;

class UnrecognisedActionTypeFactory implements ActionTypeFactoryInterface
{
    public function handles(string $type): bool
    {
        return true;
    }

    public function createFromTypeAndArguments(
        string $type,
        string $arguments,
        PageProviderInterface $pageProvider
    ): ActionInterface {
        return new UnrecognisedAction($type, $arguments);
    }
}
