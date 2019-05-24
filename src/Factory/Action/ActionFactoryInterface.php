<?php

namespace webignition\BasilParser\Factory\Action;

use webignition\BasilParser\Model\Action\ActionInterface;
use webignition\BasilParser\PageCollection\PageCollectionInterface;

interface ActionFactoryInterface
{
    public function handles(string $type): bool;

    /**
     * @param string $actionString
     * @param PageCollectionInterface $pages
     *
     * @return ActionInterface
     */
    public function createFromActionString(string $actionString, PageCollectionInterface $pages): ActionInterface;

    /**
     * @param string $type
     * @param string $arguments
     * @param PageCollectionInterface $pages
     *
     * @return ActionInterface
     */
    public function createFromTypeAndArguments(
        string $type,
        string $arguments,
        PageCollectionInterface $pages
    ): ActionInterface;
}
