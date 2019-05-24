<?php

namespace webignition\BasilParser\Factory\Action;

use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Factory\IdentifierFactory;
use webignition\BasilParser\Model\Action\ActionInterface;
use webignition\BasilParser\Model\Action\ActionTypes;
use webignition\BasilParser\Model\Action\InteractionAction;
use webignition\BasilParser\Model\Page\PageInterface;

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

    /**
     * @param string $type
     * @param string $arguments
     * @param PageInterface[] $pages
     *
     * @return ActionInterface
     *
     * @throws MalformedPageElementReferenceException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     */
    protected function doCreateFromTypeAndArguments(string $type, string $arguments, array $pages): ActionInterface
    {
        return new InteractionAction($type, $this->identifierFactory->create($arguments, $pages), $arguments);
    }
}
