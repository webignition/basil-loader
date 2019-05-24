<?php

namespace webignition\BasilParser\Factory\Action;

use webignition\BasilParser\Model\Action\ActionInterface;
use webignition\BasilParser\Model\Page\PageInterface;

interface ActionFactoryInterface
{
    public function handles(string $type): bool;

    /**
     * @param string $actionString
     * @param PageInterface[] $pages
     *
     * @return ActionInterface
     */
    public function createFromActionString(string $actionString, array $pages): ActionInterface;

    /**
     * @param string $type
     * @param string $arguments
     * @param PageInterface[] $pages
     *
     * @return ActionInterface
     */
    public function createFromTypeAndArguments(string $type, string $arguments, array $pages): ActionInterface;
}
