<?php

namespace webignition\BasilParser\Resolver;

use webignition\BasilModel\Identifier\IdentifierInterface;
use webignition\BasilModel\Identifier\IdentifierTypes;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModelFactory\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Provider\Page\PageProviderInterface;

class IdentifierResolver
{
    public static function createResolver(): IdentifierResolver
    {
        return new IdentifierResolver();
    }

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
        if (IdentifierTypes::PAGE_ELEMENT_REFERENCE !== $identifier->getType()) {
            return $identifier;
        }

        $value = $identifier->getValue();

        if (!$value instanceof ObjectValue) {
            return $identifier;
        }

        $page = $pageProvider->findPage($value->getObjectName());
        $elementIdentifier = $page->getIdentifier($value->getObjectProperty());

        if ($elementIdentifier instanceof IdentifierInterface) {
            return $elementIdentifier;
        }

        throw new UnknownPageElementException($value->getObjectName(), $value->getObjectProperty());
    }
}
