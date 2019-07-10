<?php

namespace webignition\BasilParser\Resolver;

use webignition\BasilModel\Identifier\IdentifierInterface;
use webignition\BasilModel\Identifier\IdentifierTypes;
use webignition\BasilModel\PageElementReference\PageElementReference;
use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Provider\Page\PageProviderInterface;

class IdentifierResolver
{
    /**
     * @param IdentifierInterface $identifier
     * @param PageProviderInterface $pageProvider
     *
     * @return IdentifierInterface
     *
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievablePageException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     */
    public function resolve(
        IdentifierInterface $identifier,
        PageProviderInterface $pageProvider
    ): IdentifierInterface {
        if (IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE !== $identifier->getType()) {
            return $identifier;
        }

        $pageElementReference = new PageElementReference($identifier->getValue()->getValue());

        $page = $pageProvider->findPage($pageElementReference->getImportName());
        $elementIdentifier = $page->getElementIdentifier($pageElementReference->getElementName());

        if ($elementIdentifier instanceof IdentifierInterface) {
            $identifierName = $identifier->getName();

            return null === $identifierName
                ? $elementIdentifier
                : $elementIdentifier->withName($identifierName);
        }

        throw new UnknownPageElementException(
            $pageElementReference->getImportName(),
            $pageElementReference->getElementName()
        );
    }
}
