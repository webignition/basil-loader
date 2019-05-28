<?php

namespace webignition\BasilParser\Factory\Action;

use webignition\BasilParser\Model\Action\ActionInterface;
use webignition\BasilParser\PageProvider\PageProviderInterface;

interface ActionTypeFactoryInterface
{
    public function handles(string $type): bool;

    /**
     * @param string $type
     * @param string $arguments
     * @param PageProviderInterface $pageProvider
     *
     * @return ActionInterface
     */
    public function createForActionType(
        string $type,
        string $arguments,
        PageProviderInterface $pageProvider
    ): ActionInterface;
}
