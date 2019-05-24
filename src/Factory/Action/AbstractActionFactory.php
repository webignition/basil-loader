<?php

namespace webignition\BasilParser\Factory\Action;

use webignition\BasilParser\Model\Action\ActionInterface;
use webignition\BasilParser\Model\Page\PageInterface;

abstract class AbstractActionFactory implements ActionFactoryInterface
{
    public function handles(string $type): bool
    {
        return in_array($type, $this->getHandledActionTypes());
    }

    /**
     * @return string[]
     */
    abstract protected function getHandledActionTypes(): array;

    /**
     * @param string $type
     * @param string $arguments
     * @param PageInterface[] $pages
     *
     * @return ActionInterface
     */
    abstract protected function doCreateFromTypeAndArguments(
        string $type,
        string $arguments,
        array $pages
    ): ActionInterface;

    public function createFromActionString(string $actionString, array $pages): ActionInterface
    {
        $actionString = trim($actionString);

        $type = $actionString;
        $arguments = '';

        if (mb_substr_count($actionString, ' ') > 0) {
            list($type, $arguments) = explode(' ', $actionString, 2);
        }

        return $this->createFromTypeAndArguments($type, $arguments, $pages);
    }

    public function createFromTypeAndArguments(string $type, string $arguments, array $pages): ActionInterface
    {
        if (!$this->handles($type)) {
            throw new \RuntimeException('Invalid action type');
        }

        return $this->doCreateFromTypeAndArguments($type, $arguments, $pages);
    }
}
