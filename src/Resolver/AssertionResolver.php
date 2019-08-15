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
     * @param IdentifierCollectionInterface $identifierCollection
     *
     * @return AssertionInterface
     *
     * @throws InvalidPageElementIdentifierException
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievablePageException
     * @throws UnknownElementException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     */
    public function resolve(
        AssertionInterface $assertion,
        PageProviderInterface $pageProvider,
        IdentifierCollectionInterface $identifierCollection
    ): AssertionInterface {
        $examinedValue = $assertion->getExaminedValue();

        if (!$examinedValue instanceof ObjectValue) {
            return $assertion;
        }

        $examinedValueType = $examinedValue->getType();

        if (ValueTypes::PAGE_ELEMENT_REFERENCE === $examinedValueType) {
            $resolvedIdentifier = $this->pageElementReferenceResolver->resolve($examinedValue, $pageProvider);

            return $assertion->withExaminedValue(new ElementValue($resolvedIdentifier));
        }

        if (ValueTypes::ELEMENT_PARAMETER === $examinedValueType) {
            $elementName = $examinedValue->getObjectProperty();

            $resolvedIdentifier = $identifierCollection->getIdentifier($elementName);

            if (!$resolvedIdentifier instanceof ElementIdentifierInterface) {
                throw new UnknownElementException($elementName);
            }

            return $assertion->withExaminedValue(new ElementValue($resolvedIdentifier));
        }

        return $assertion;
    }
}
