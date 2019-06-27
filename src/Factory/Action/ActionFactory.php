<?php

namespace webignition\BasilParser\Factory\Action;

use webignition\BasilModel\Action\ActionInterface;
use webignition\BasilModel\Action\UnrecognisedAction;
use webignition\BasilParser\Provider\Page\PageProviderInterface;

class ActionFactory
{
    /**
     * @var ActionTypeFactoryInterface[]
     */
    private $actionTypeFactories = [];

    public function addActionTypeFactory(ActionTypeFactoryInterface $actionTypeFactory)
    {
        $this->actionTypeFactories[] = $actionTypeFactory;
    }

    public function createFromActionString(string $actionString, PageProviderInterface $pageProvider): ActionInterface
    {
        $actionString = trim($actionString);

        $type = $actionString;
        $arguments = '';

        if (mb_substr_count($actionString, ' ') > 0) {
            list($type, $arguments) = explode(' ', $actionString, 2);
        }

        $actionTypeFactory = $this->findActionTypeFactory($type);

        if ($actionTypeFactory instanceof ActionTypeFactoryInterface) {
            return $actionTypeFactory->createForActionType($type, $arguments, $pageProvider);
        }

        return new UnrecognisedAction($type, $arguments);
    }

    private function findActionTypeFactory(string $type): ?ActionTypeFactoryInterface
    {
        foreach ($this->actionTypeFactories as $typeParser) {
            if ($typeParser->handles($type)) {
                return $typeParser;
            }
        }

        return null;
    }
}
