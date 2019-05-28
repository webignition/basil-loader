<?php

namespace webignition\BasilParser\Factory\Action;

use webignition\BasilParser\Model\Action\ActionInterface;
use webignition\BasilParser\PageProvider\PageProviderInterface;

abstract class AbstractActionTypeFactory implements ActionTypeFactoryInterface
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
     * @param PageProviderInterface $pageProvider
     *
     * @return ActionInterface
     */
    abstract protected function doCreateFromTypeAndArguments(
        string $type,
        string $arguments,
        PageProviderInterface $pageProvider
    ): ActionInterface;

    public function createFromTypeAndArguments(
        string $type,
        string $arguments,
        PageProviderInterface $pageProvider
    ): ActionInterface {
        if (!$this->handles($type)) {
            throw new \RuntimeException('Invalid action type');
        }

        return $this->doCreateFromTypeAndArguments($type, $arguments, $pageProvider);
    }
}
