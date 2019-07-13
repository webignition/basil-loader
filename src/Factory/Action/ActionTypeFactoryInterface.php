<?php

namespace webignition\BasilParser\Factory\Action;

use webignition\BasilModel\Action\ActionInterface;

interface ActionTypeFactoryInterface
{
    public function handles(string $type): bool;

    /**
     * @param string $actionString
     * @param string $type
     * @param string $arguments
     *
     * @return ActionInterface
     */
    public function createForActionType(string $actionString, string $type, string $arguments): ActionInterface;
}
