<?php

namespace webignition\BasilParser\Factory\Action;

use webignition\BasilParser\Factory\IdentifierFactory;
use webignition\BasilParser\Model\Action\ActionInterface;
use webignition\BasilParser\Model\Action\ActionTypes;
use webignition\BasilParser\Model\Action\InteractionAction;

class InteractionActionFactory implements ActionFactoryInterface
{
    private $handledTypes = [
        ActionTypes::CLICK,
        ActionTypes::SUBMIT,
        ActionTypes::WAIT_FOR,
    ];

    private $identifierFactory;

    public function __construct(?IdentifierFactory $identifierFactory = null)
    {
        $identifierFactory = $identifierFactory ?? new IdentifierFactory();

        $this->identifierFactory = $identifierFactory;
    }

    public function handles(string $type): bool
    {
        return in_array($type, $this->handledTypes);
    }

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

        return new InteractionAction(
            $type,
            $this->identifierFactory->create($arguments)
        );
    }
}
