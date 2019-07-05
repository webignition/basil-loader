<?php

namespace webignition\BasilParser\Resolver;

use webignition\BasilModel\Action\ActionInterface;
use webignition\BasilModel\IdentifierContainerInterface;
use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Provider\Page\PageProviderInterface;

class ActionResolver
{
    /**
     * @var PageModelElementIdentifierResolver
     */
    private $pageModelElementIdentifierResolver;

    public function __construct(PageModelElementIdentifierResolver $pageModelElementIdentifierResolver)
    {
        $this->pageModelElementIdentifierResolver = $pageModelElementIdentifierResolver;
    }

    /**
     * @param ActionInterface $action
     * @param PageProviderInterface $pageProvider
     *
     * @return ActionInterface
     *
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievablePageException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     */
    public function resolve(ActionInterface $action, PageProviderInterface $pageProvider): ActionInterface
    {
        if ($action instanceof IdentifierContainerInterface) {
            $resolvedAction = $this->pageModelElementIdentifierResolver->resolve($action, $pageProvider);

            if ($resolvedAction instanceof ActionInterface) {
                return $resolvedAction;
            }
        }

        return $action;
    }
}
