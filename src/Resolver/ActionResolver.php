<?php

namespace webignition\BasilParser\Resolver;

use webignition\BasilModel\Action\ActionInterface;
use webignition\BasilModel\Action\InteractionActionInterface;
use webignition\BasilModel\Identifier\IdentifierInterface;
use webignition\BasilModelFactory\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Provider\Page\PageProviderInterface;

class ActionResolver
{
    private $identifierResolver;

    public function __construct(IdentifierResolver $identifierResolver)
    {
        $this->identifierResolver = $identifierResolver;
    }

    public static function createResolver(): ActionResolver
    {
        return new ActionResolver(
            IdentifierResolver::createResolver()
        );
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
        if (!$action instanceof InteractionActionInterface) {
            return $action;
        }

        $identifier = $action->getIdentifier();

        if (!$identifier instanceof IdentifierInterface) {
            return $action;
        }

        $resolvedIdentifier = $this->identifierResolver->resolve($identifier, $pageProvider);

        if ($resolvedIdentifier === $identifier) {
            return $action;
        }

        return $action->withIdentifier($resolvedIdentifier);
    }
}
