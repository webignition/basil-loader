<?php

namespace webignition\BasilParser\Resolver;

use webignition\BasilModel\Assertion\AssertionInterface;
use webignition\BasilModel\Identifier\AttributeIdentifier;
use webignition\BasilModel\Identifier\ElementIdentifierInterface;
use webignition\BasilModel\Identifier\IdentifierCollectionInterface;
use webignition\BasilModel\Value\AttributeValue;
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
    const ELEMENT_NAME_ATTRIBUTE_NAME_DELIMITER = '.';

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
    public function resolvePageElementReferences(
        AssertionInterface $assertion,
        PageProviderInterface $pageProvider
    ): AssertionInterface {
        $examinedValue = $assertion->getExaminedValue();

        if ($examinedValue instanceof ObjectValue && ValueTypes::PAGE_ELEMENT_REFERENCE === $examinedValue->getType()) {
            $resolvedIdentifier = $this->pageElementReferenceResolver->resolve($examinedValue, $pageProvider);

            $assertion = $assertion->withExaminedValue(new ElementValue($resolvedIdentifier));
        }

        $expectedValue = $assertion->getExpectedValue();

        if ($expectedValue instanceof ObjectValue && ValueTypes::PAGE_ELEMENT_REFERENCE === $expectedValue->getType()) {
            $resolvedIdentifier = $this->pageElementReferenceResolver->resolve($expectedValue, $pageProvider);

            $assertion = $assertion->withExpectedValue(new ElementValue($resolvedIdentifier));
        }

        return $assertion;
    }

    /**
     * @param AssertionInterface $assertion
     * @param IdentifierCollectionInterface $identifierCollection
     *
     * @return AssertionInterface
     *
     * @throws UnknownElementException
     */
    public function resolveElementParameters(
        AssertionInterface $assertion,
        IdentifierCollectionInterface $identifierCollection
    ): AssertionInterface {
        $examinedValue = $assertion->getExaminedValue();

        if ($examinedValue instanceof ObjectValue && ValueTypes::ELEMENT_PARAMETER === $examinedValue->getType()) {
            $elementName = $examinedValue->getObjectProperty();
            $resolvedIdentifier = $identifierCollection->getIdentifier($elementName);

            if (!$resolvedIdentifier instanceof ElementIdentifierInterface) {
                throw new UnknownElementException($elementName);
            }

            $assertion = $assertion->withExaminedValue(new ElementValue($resolvedIdentifier));
        }

        $expectedValue = $assertion->getExpectedValue();

        if ($expectedValue instanceof ObjectValue && ValueTypes::ELEMENT_PARAMETER === $expectedValue->getType()) {
            $elementName = $expectedValue->getObjectProperty();
            $identifier = $this->findElementIdentifier($identifierCollection, $elementName);

            $assertion = $assertion->withExpectedValue(new ElementValue($identifier));
        }

        return $assertion;
    }

    /**
     * @param AssertionInterface $assertion
     * @param IdentifierCollectionInterface $identifierCollection
     *
     * @return AssertionInterface
     *
     * @throws UnknownElementException
     */
    public function resolveAttributeParameters(
        AssertionInterface $assertion,
        IdentifierCollectionInterface $identifierCollection
    ): AssertionInterface {
        $examinedValue = $assertion->getExaminedValue();

        if ($examinedValue instanceof ObjectValue && ValueTypes::ATTRIBUTE_PARAMETER === $examinedValue->getType()) {
            $objectProperty = $examinedValue->getObjectProperty();

            if (substr_count($objectProperty, self::ELEMENT_NAME_ATTRIBUTE_NAME_DELIMITER) > 0) {
                list($elementName, $attributeName) = explode('.', $examinedValue->getObjectProperty());

                $elementIdentifier = $this->findElementIdentifier($identifierCollection, $elementName);
                $attributeIdentifier = new AttributeIdentifier($elementIdentifier, $attributeName);

                $assertion = $assertion->withExaminedValue(new AttributeValue($attributeIdentifier));
            }
        }

        $expectedValue = $assertion->getExpectedValue();

        if ($expectedValue instanceof ObjectValue && ValueTypes::ATTRIBUTE_PARAMETER === $expectedValue->getType()) {
            $objectProperty = $expectedValue->getObjectProperty();

            if (substr_count($objectProperty, self::ELEMENT_NAME_ATTRIBUTE_NAME_DELIMITER) > 0) {
                list($elementName, $attributeName) = explode('.', $expectedValue->getObjectProperty());

                $elementIdentifier = $this->findElementIdentifier($identifierCollection, $elementName);
                $attributeIdentifier = new AttributeIdentifier($elementIdentifier, $attributeName);

                $assertion = $assertion->withExpectedValue(new AttributeValue($attributeIdentifier));
            }
        }

        return $assertion;
    }

    /**
     * @param IdentifierCollectionInterface $identifierCollection
     *
     * @param string $elementName
     *
     * @return ElementIdentifierInterface
     * @throws UnknownElementException
     */
    private function findElementIdentifier(
        IdentifierCollectionInterface $identifierCollection,
        string $elementName
    ): ElementIdentifierInterface {
        $identifier = $identifierCollection->getIdentifier($elementName);

        if (!$identifier instanceof ElementIdentifierInterface) {
            throw new UnknownElementException($elementName);
        }

        return $identifier;
    }
}
