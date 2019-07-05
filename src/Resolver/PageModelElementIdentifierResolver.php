<?php

namespace webignition\BasilParser\Resolver;

use webignition\BasilModel\Identifier\IdentifierInterface;
use webignition\BasilModel\Identifier\IdentifierTypes;
use webignition\BasilModel\IdentifierContainerInterface;
use webignition\BasilModel\PageElementReference\PageElementReference;
use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Provider\Page\PageProviderInterface;

class PageModelElementIdentifierResolver
{
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

        if (IdentifierTypes::PAGE_MODEL_ELEMENT_REFERENCE === $identifier->getType()) {
            $pageElementReference = new PageElementReference($identifier->getValue()->getValue());

            $page = $pageProvider->findPage($pageElementReference->getImportName());
            $elementIdentifier = $page->getElementIdentifier($pageElementReference->getElementName());

            if ($elementIdentifier instanceof IdentifierInterface) {
                return $identifierContainer->withIdentifier($elementIdentifier);
            }
        }

        return $identifierContainer;
    }
}
