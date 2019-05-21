<?php

namespace webignition\BasilParser\Factory\Action;

use webignition\BasilParser\Factory\IdentifierFactory;
use webignition\BasilParser\Model\Action\ActionInterface;
use webignition\BasilParser\Model\Action\ActionTypes;
use webignition\BasilParser\Model\Action\InteractionAction;

class InteractionActionFactory extends AbstractActionFactory implements ActionFactoryInterface
{
    private $identifierFactory;

    public function __construct(?IdentifierFactory $identifierFactory = null)
    {
        $identifierFactory = $identifierFactory ?? new IdentifierFactory();

        $this->identifierFactory = $identifierFactory;
    }

    protected function getHandledActionTypes(): array
    {
        return [
            ActionTypes::CLICK,
            ActionTypes::SUBMIT,
            ActionTypes::WAIT_FOR,
        ];
    }

    protected function doCreateFromTypeAndArguments(string $type, string $arguments): ActionInterface
    {
        return new InteractionAction($type, $this->identifierFactory->create($arguments), $arguments);
    }
}
