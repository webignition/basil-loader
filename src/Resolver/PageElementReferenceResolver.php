<?php

namespace webignition\BasilParser\Resolver;

use webignition\BasilModel\Identifier\IdentifierInterface;
use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilModelFactory\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Provider\Page\PageProviderInterface;

class PageElementReferenceResolver
{
    public static function createResolver(): PageElementReferenceResolver
    {
        return new PageElementReferenceResolver();
    }

    /**
     * @param ObjectValueInterface $value
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
        ObjectValueInterface $value,
        PageProviderInterface $pageProvider
    ): ?IdentifierInterface {
        $page = $pageProvider->findPage($value->getObjectName());
        $elementIdentifier = $page->getIdentifier($value->getObjectProperty());

        if ($elementIdentifier instanceof IdentifierInterface) {
            return $elementIdentifier;
        }

        throw new UnknownPageElementException($value->getObjectName(), $value->getObjectProperty());
    }
}
