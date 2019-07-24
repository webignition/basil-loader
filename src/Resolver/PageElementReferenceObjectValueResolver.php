<?php

namespace webignition\BasilParser\Resolver;

use webignition\BasilModel\Identifier\ElementIdentifier;
use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilModel\Value\ValueInterface;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilModelFactory\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Provider\Page\PageProviderInterface;

class PageElementReferenceObjectValueResolver
{
    /**
     * @param ObjectValueInterface $value
     * @param PageProviderInterface $pageProvider
     *
     * @return ValueInterface|null
     *
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievablePageException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     */
    public function resolve(
        ObjectValueInterface $value,
        PageProviderInterface $pageProvider
    ): ValueInterface {
        if ($value->getType() !== ValueTypes::PAGE_ELEMENT_REFERENCE) {
            return $value;
        }

        $page = $pageProvider->findPage($value->getObjectName());
        $elementIdentifier = $page->getIdentifier($value->getObjectProperty());

        if ($elementIdentifier instanceof ElementIdentifier) {
            return $elementIdentifier->getValue();
        }

        throw new UnknownPageElementException($value->getObjectName(), $value->getObjectProperty());
    }
}
