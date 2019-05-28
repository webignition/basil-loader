<?php

namespace webignition\BasilParser\Factory\Action;

use webignition\BasilParser\Model\Action\ActionInterface;
use webignition\BasilParser\PageProvider\PageProviderInterface;

class ActionFactory
{
    /**
     * @var ActionFactoryInterface[]
     */
    private $actionTypeFactories;

    /**
     * @var UnrecognisedActionFactory
     */
    private $unrecognisedActionFactory;

    public function __construct()
    {
        $this->actionTypeFactories[] = new InteractionActionFactory();
        $this->actionTypeFactories[] = new WaitActionFactory();
        $this->actionTypeFactories[] = new NoArgumentsActionFactory();
        $this->actionTypeFactories[] = new InputActionFactory();

        $this->unrecognisedActionFactory = new UnrecognisedActionFactory();
    }

    public function createFromActionString(string $actionString, PageProviderInterface $pageProvider): ActionInterface
    {
        $actionString = trim($actionString);

        $type = $actionString;
        $arguments = '';

        if (mb_substr_count($actionString, ' ') > 0) {
            list($type, $arguments) = explode(' ', $actionString, 2);
        }

        return $this->findActionTypeFactory($type)->createFromTypeAndArguments(
            $type,
            $arguments,
            $pageProvider
        );
    }

    private function findActionTypeFactory(string $type): ActionFactoryInterface
    {
        foreach ($this->actionTypeFactories as $typeParser) {
            if ($typeParser->handles($type)) {
                return $typeParser;
            }
        }

        return $this->unrecognisedActionFactory;
    }
}
