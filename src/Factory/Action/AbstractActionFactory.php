<?php

namespace webignition\BasilParser\Factory\Action;

use webignition\BasilParser\Model\Action\ActionInterface;

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

    abstract protected function doCreateFromTypeAndArguments(string $type, string $arguments): ActionInterface;

    public function createFromActionString(string $actionString): ActionInterface
    {
        list($type, $arguments) = explode(' ', $actionString, 2);

        return $this->createFromTypeAndArguments($type, $arguments);
    }

    public function createFromTypeAndArguments(string $type, string $arguments): ActionInterface
    {
        if (!$this->handles($type)) {
            throw new \RuntimeException('Invalid action type');
        }

        return $this->doCreateFromTypeAndArguments($type, $arguments);
    }
}
