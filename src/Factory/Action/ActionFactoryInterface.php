<?php

namespace webignition\BasilParser\Factory\Action;

use webignition\BasilParser\Model\Action\ActionInterface;
use webignition\BasilParser\PageProvider\PageProviderInterface;

interface ActionFactoryInterface
{
    public function handles(string $type): bool;

    /**
     * @param string $actionString
     * @param PageProviderInterface $pageProvider
     *
     * @return ActionInterface
     */
    public function createFromActionString(string $actionString, PageProviderInterface $pageProvider): ActionInterface;

    /**
     * @param string $type
     * @param string $arguments
     * @param PageProviderInterface $pageProvider
     *
     * @return ActionInterface
     */
    public function createFromTypeAndArguments(
        string $type,
        string $arguments,
        PageProviderInterface $pageProvider
    ): ActionInterface;
}
