<?php

namespace webignition\BasilParser\Resolver;

use webignition\BasilModel\Action\ActionInterface;
use webignition\BasilModel\Action\InteractionActionInterface;
use webignition\BasilModel\Identifier\IdentifierCollectionInterface;
use webignition\BasilModel\Identifier\IdentifierInterface;
use webignition\BasilModelFactory\InvalidPageElementIdentifierException;
use webignition\BasilModelFactory\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownElementException;
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
     * @param IdentifierCollectionInterface $identifierCollection
     *
     * @return ActionInterface
     *
     * @throws InvalidPageElementIdentifierException
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievablePageException
     * @throws UnknownElementException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     */
    public function resolve(
        ActionInterface $action,
        PageProviderInterface $pageProvider,
        IdentifierCollectionInterface $identifierCollection
    ): ActionInterface {
        if (!$action instanceof InteractionActionInterface) {
            return $action;
        }

        $identifier = $action->getIdentifier();

        if (!$identifier instanceof IdentifierInterface) {
            return $action;
        }

        $resolvedIdentifier = $this->identifierResolver->resolvePageElementReference($identifier, $pageProvider);
        $resolvedIdentifier = $this->identifierResolver->resolveElementParameter(
            $resolvedIdentifier,
            $identifierCollection
        );

        if ($resolvedIdentifier === $identifier) {
            return $action;
        }

        return $action->withIdentifier($resolvedIdentifier);
    }
}
