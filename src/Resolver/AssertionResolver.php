<?php

namespace webignition\BasilParser\Resolver;

use webignition\BasilModel\Assertion\AssertionInterface;
use webignition\BasilModel\Identifier\ElementIdentifierInterface;
use webignition\BasilModel\Identifier\IdentifierCollectionInterface;
use webignition\BasilModel\Value\ElementValue;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilModelFactory\InvalidPageElementIdentifierException;
use webignition\BasilModelFactory\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownElementException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Provider\Page\PageProviderInterface;

class AssertionResolver
{
    private $pageElementReferenceResolver;

    public function __construct(PageElementReferenceResolver $pageElementReferenceResolver)
    {
        $this->pageElementReferenceResolver = $pageElementReferenceResolver;
    }

    public static function createResolver(): AssertionResolver
    {
        return new AssertionResolver(
            PageElementReferenceResolver::createResolver()
        );
    }

    /**
     * @param AssertionInterface $assertion
     * @param PageProviderInterface $pageProvider
     *
     * @return AssertionInterface
     *
     * @throws InvalidPageElementIdentifierException
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievablePageException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     */
    public function resolvePageElementReferenceExaminedValue(
        AssertionInterface $assertion,
        PageProviderInterface $pageProvider
    ): AssertionInterface {
        $examinedValue = $assertion->getExaminedValue();

        if (!$examinedValue instanceof ObjectValue) {
            return $assertion;
        }

        if (ValueTypes::PAGE_ELEMENT_REFERENCE !== $examinedValue->getType()) {
            return $assertion;
        }

        $resolvedIdentifier = $this->pageElementReferenceResolver->resolve($examinedValue, $pageProvider);

        return $assertion->withExaminedValue(new ElementValue($resolvedIdentifier));
    }

    /**
     * @param AssertionInterface $assertion
     * @param IdentifierCollectionInterface $identifierCollection
     *
     * @return AssertionInterface
     *
     * @throws UnknownElementException
     */
    public function resolveElementParameterExaminedValue(
        AssertionInterface $assertion,
        IdentifierCollectionInterface $identifierCollection
    ): AssertionInterface {
        $examinedValue = $assertion->getExaminedValue();

        if (!$examinedValue instanceof ObjectValue) {
            return $assertion;
        }

        if (ValueTypes::ELEMENT_PARAMETER !== $examinedValue->getType()) {
            return $assertion;
        }

        $elementName = $examinedValue->getObjectProperty();

        $resolvedIdentifier = $identifierCollection->getIdentifier($elementName);

        if (!$resolvedIdentifier instanceof ElementIdentifierInterface) {
            throw new UnknownElementException($elementName);
        }

        return $assertion->withExaminedValue(new ElementValue($resolvedIdentifier));
    }
}
