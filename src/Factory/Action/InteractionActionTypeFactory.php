<?php

namespace webignition\BasilParser\Factory\Action;

use webignition\BasilModel\Action\ActionInterface;
use webignition\BasilModel\Action\ActionTypes;
use webignition\BasilModel\Action\InteractionAction;
use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Factory\IdentifierFactory;

class InteractionActionTypeFactory extends AbstractActionTypeFactory implements ActionTypeFactoryInterface
{
    private $identifierFactory;

    public function __construct(IdentifierFactory $identifierFactory)
    {
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

    /**
     * @param string $type
     * @param string $arguments
     *
     * @return ActionInterface
     *
     * @throws MalformedPageElementReferenceException
     */
    protected function doCreateForActionType(string $type, string $arguments): ActionInterface
    {
        return new InteractionAction($type, $this->identifierFactory->create($arguments), $arguments);
    }
}
