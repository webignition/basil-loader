<?php

namespace webignition\BasilParser\Resolver;

use webignition\BasilModel\Identifier\IdentifierInterface;
use webignition\BasilModel\IdentifierContainerInterface;
use webignition\BasilModelFactory\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Provider\Page\PageProviderInterface;

class IdentifierContainerIdentifierResolver
{
    private $identifierResolver;

    public function __construct(IdentifierResolver $identifierResolver)
    {
        $this->identifierResolver = $identifierResolver;
    }

    /**
     * @param IdentifierContainerInterface $identifierContainer
     * @param PageProviderInterface $pageProvider
     *
     * @return IdentifierContainerInterface
     *
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievablePageException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     */
    public function resolve(
        IdentifierContainerInterface $identifierContainer,
        PageProviderInterface $pageProvider
    ): IdentifierContainerInterface {
        $identifier = $identifierContainer->getIdentifier();

        if (!$identifier instanceof IdentifierInterface) {
            return $identifierContainer;
        }

        $resolvedIdentifier = $this->identifierResolver->resolve($identifier, $pageProvider);

        return $resolvedIdentifier === $identifier
            ? $identifierContainer
            : $identifierContainer->withIdentifier($resolvedIdentifier);
    }
}
