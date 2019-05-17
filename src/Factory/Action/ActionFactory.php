<?php

namespace webignition\BasilParser\Factory\Action;

use webignition\BasilParser\Model\Action\ActionInterface;
use webignition\BasilParser\Model\Action\ActionTypes;

class ActionFactory extends AbstractActionFactory implements ActionFactoryInterface
{
    /**
     * @var ActionFactoryInterface[]
     */
    private $actionTypeFactories;

    /**
     * @var UnrecognisedActionFactory
     */
    private $unrecognisedActionFactory;

    public function __construct(array $actionTypeFactories)
    {
        foreach ($actionTypeFactories as $actionTypeFactory) {
            if ($actionTypeFactory instanceof ActionFactoryInterface) {
                $this->actionTypeFactories[] = $actionTypeFactory;
            }
        }

        $this->unrecognisedActionFactory = new UnrecognisedActionFactory();
    }

    public function handles(string $type): bool
    {
        return true;
    }

    protected function getHandledActionTypes(): array
    {
        return ActionTypes::ALL;
    }

    protected function doCreateFromTypeAndArguments(string $type, string $arguments): ActionInterface
    {
        return $this->findTypeSpecificActionFactory($type)->createFromTypeAndArguments($type, $arguments);
    }

    private function findTypeSpecificActionFactory(string $type): ActionFactoryInterface
    {
        foreach ($this->actionTypeFactories as $typeParser) {
            if ($typeParser->handles($type)) {
                return $typeParser;
            }
        }

        return $this->unrecognisedActionFactory;
    }
}
