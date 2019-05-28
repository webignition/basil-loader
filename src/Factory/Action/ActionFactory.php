<?php

namespace webignition\BasilParser\Factory\Action;

use webignition\BasilParser\Model\Action\ActionInterface;
use webignition\BasilParser\PageProvider\PageProviderInterface;

class ActionFactory
{
    /**
     * @var ActionTypeFactoryInterface[]
     */
    private $actionTypeFactories;

    /**
     * @var UnrecognisedActionTypeFactory
     */
    private $unrecognisedActionFactory;

    public function __construct()
    {
        $this->actionTypeFactories[] = new InteractionActionTypeFactory();
        $this->actionTypeFactories[] = new WaitActionTypeFactory();
        $this->actionTypeFactories[] = new NoArgumentsActionTypeFactory();
        $this->actionTypeFactories[] = new InputActionTypeFactory();

        $this->unrecognisedActionFactory = new UnrecognisedActionTypeFactory();
    }

    public function createFromActionString(string $actionString, PageProviderInterface $pageProvider): ActionInterface
    {
        $actionString = trim($actionString);

        $type = $actionString;
        $arguments = '';

        if (mb_substr_count($actionString, ' ') > 0) {
            list($type, $arguments) = explode(' ', $actionString, 2);
        }

        return $this->findActionTypeFactory($type)->createForActionType(
            $type,
            $arguments,
            $pageProvider
        );
    }

    private function findActionTypeFactory(string $type): ActionTypeFactoryInterface
    {
        foreach ($this->actionTypeFactories as $typeParser) {
            if ($typeParser->handles($type)) {
                return $typeParser;
            }
        }

        return $this->unrecognisedActionFactory;
    }
}
