<?php

namespace webignition\BasilParser\Factory\Action;

use webignition\BasilParser\Model\Action\ActionInterface;
use webignition\BasilParser\Model\Action\ActionTypes;

class ActionFactory implements ActionFactoryInterface
{
    /**
     * @var ActionFactoryInterface[]
     */
    private $actionTypeFactories;

    public function __construct(array $actionTypeFactories)
    {
        foreach ($actionTypeFactories as $actionTypeFactory) {
            if ($actionTypeFactory instanceof ActionFactoryInterface) {
                $this->actionTypeFactories[] = $actionTypeFactory;
            }
        }
    }

    public function handles(string $type): bool
    {
        return in_array($type, ActionTypes::ALL);
    }

    public function createFromActionString(string $actionString): ActionInterface
    {
        list($type, $arguments) = explode(' ', $actionString, 2);

        $typeParser = $this->findTypeParser($type);

        if (!$typeParser instanceof ActionFactoryInterface) {
            throw new \RuntimeException('Invalid action type');
        }

        return $typeParser->createFromTypeAndArguments($type, $arguments);
    }

    public function createFromTypeAndArguments(string $type, string $arguments): ActionInterface
    {
        $typeParser = $this->findTypeParser($type);

        if (!$typeParser instanceof ActionFactoryInterface) {
            throw new \RuntimeException('Invalid action type');
        }

        return $typeParser->createFromTypeAndArguments($type, $arguments);
    }

    private function findTypeParser(string $type): ?ActionFactoryInterface
    {
        foreach ($this->actionTypeFactories as $typeParser) {
            if ($typeParser->handles($type)) {
                return $typeParser;
            }
        }

        return null;
    }
}
