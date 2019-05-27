<?php

namespace webignition\BasilParser\Factory\Action;

use webignition\BasilParser\Model\Action\ActionInterface;
use webignition\BasilParser\Model\Action\UnrecognisedAction;
use webignition\BasilParser\PageProvider\PageProviderInterface;

class UnrecognisedActionFactory implements ActionFactoryInterface
{
    public function handles(string $type): bool
    {
        return true;
    }

    public function createFromActionString(string $actionString, PageProviderInterface $pageProvider): ActionInterface
    {
        list($type, $arguments) = explode(' ', $actionString, 2);

        return $this->createFromTypeAndArguments($type, $arguments, $pageProvider);
    }

    public function createFromTypeAndArguments(
        string $type,
        string $arguments,
        PageProviderInterface $pageProvider
    ): ActionInterface {
        return new UnrecognisedAction($type, $arguments);
    }
}
