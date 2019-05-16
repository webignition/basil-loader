<?php

namespace webignition\BasilParser\Factory\Action;

use webignition\BasilParser\Model\Action\Action;
use webignition\BasilParser\Model\Action\ActionInterface;
use webignition\BasilParser\Model\Action\ActionTypes;

class ActionFactory extends AbstractActionFactory implements ActionFactoryInterface
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

    protected function getHandledActionTypes(): array
    {
        return ActionTypes::ALL;
    }

    protected function doCreateFromTypeAndArguments(string $type, string $arguments): ActionInterface
    {
        $typeSpecificActionFactory = $this->findTypeSpecificActionFactory($type);

        return $typeSpecificActionFactory instanceof ActionFactoryInterface
            ? $typeSpecificActionFactory->createFromTypeAndArguments($type, $arguments)
            : new Action($type);
    }

    private function findTypeSpecificActionFactory(string $type): ?ActionFactoryInterface
    {
        foreach ($this->actionTypeFactories as $typeParser) {
            if ($typeParser->handles($type)) {
                return $typeParser;
            }
        }

        return null;
    }
}
