<?php

namespace webignition\BasilParser\Factory\Action;

use webignition\BasilParser\Model\Action\ActionInterface;

interface ActionFactoryInterface
{
    public function handles(string $type): bool;
    public function createFromActionString(string $actionString): ActionInterface;
    public function createFromTypeAndArguments(string $type, string $arguments): ActionInterface;
}
