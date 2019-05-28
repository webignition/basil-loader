<?php

namespace webignition\BasilParser\Factory\Action;

use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Factory\IdentifierFactory;
use webignition\BasilParser\Model\Action\ActionInterface;
use webignition\BasilParser\Model\Action\ActionTypes;
use webignition\BasilParser\Model\Action\InteractionAction;
use webignition\BasilParser\PageProvider\PageProviderInterface;

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
     * @param PageProviderInterface $pageProvider
     *
     * @return ActionInterface
     *
     * @throws MalformedPageElementReferenceException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     * @throws NonRetrievablePageException
     */
    protected function doCreateForActionType(
        string $type,
        string $arguments,
        PageProviderInterface $pageProvider
    ): ActionInterface {
        return new InteractionAction($type, $this->identifierFactory->create($arguments, $pageProvider), $arguments);
    }
}
