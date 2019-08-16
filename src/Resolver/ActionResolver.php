<?php

namespace webignition\BasilParser\Resolver;

use webignition\BasilModel\Action\ActionInterface;
use webignition\BasilModel\Action\InputActionInterface;
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
    private $valueResolver;

    public function __construct(IdentifierResolver $identifierResolver, ValueResolver $valueResolver)
    {
        $this->identifierResolver = $identifierResolver;
        $this->valueResolver = $valueResolver;
    }

    public static function createResolver(): ActionResolver
    {
        return new ActionResolver(
            IdentifierResolver::createResolver(),
            ValueResolver::createResolver()
        );
    }

    /**
     * @param ActionInterface $action
     * @param PageProviderInterface $pageProvider
     *
     * @return ActionInterface
     *
     * @throws InvalidPageElementIdentifierException
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievablePageException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     */
    public function resolvePageElementReferences(
        ActionInterface $action,
        PageProviderInterface $pageProvider
    ): ActionInterface {
        if (!$action instanceof InteractionActionInterface) {
            return $action;
        }

        $identifier = $action->getIdentifier();

        if ($identifier instanceof IdentifierInterface) {
            $resolvedIdentifier = $this->identifierResolver->resolvePageElementReference($identifier, $pageProvider);

            if ($resolvedIdentifier !== $identifier) {
                $action = $action->withIdentifier($resolvedIdentifier);
            }
        }

        if ($action instanceof InputActionInterface) {
            $action = $action->withValue(
                $this->valueResolver->resolvePageElementReference($action->getValue(), $pageProvider)
            );
        }

        return $action;
    }

    /**
     * @param ActionInterface $action
     * @param IdentifierCollectionInterface $identifierCollection
     *
     * @return ActionInterface
     *
     * @throws UnknownElementException
     */
    public function resolveElementParameters(
        ActionInterface $action,
        IdentifierCollectionInterface $identifierCollection
    ): ActionInterface {
        if (!$action instanceof InteractionActionInterface) {
            return $action;
        }

        $identifier = $action->getIdentifier();

        if ($identifier instanceof IdentifierInterface) {
            $resolvedIdentifier = $this->identifierResolver->resolveElementParameter(
                $identifier,
                $identifierCollection
            );

            $action = $action->withIdentifier($resolvedIdentifier);
        }

        if ($action instanceof InputActionInterface) {
            $action = $action->withValue(
                $this->valueResolver->resolveElementParameter($action->getValue(), $identifierCollection)
            );
        }

        return $action;
    }

    /**
     * @param ActionInterface $action
     * @param IdentifierCollectionInterface $identifierCollection
     *
     * @return ActionInterface
     *
     * @throws UnknownElementException
     */
    public function resolveAttributeParameters(
        ActionInterface $action,
        IdentifierCollectionInterface $identifierCollection
    ): ActionInterface {
        if ($action instanceof InputActionInterface) {
            $action = $action->withValue(
                $this->valueResolver->resolveAttributeParameter($action->getValue(), $identifierCollection)
            );
        }

        return $action;
    }
}
