<?php

namespace webignition\BasilParser\Resolver;

use webignition\BasilModel\Assertion\AssertionInterface;
use webignition\BasilModel\Identifier\IdentifierCollectionInterface;
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

    private $valueResolver;

    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }

    public static function createResolver(): AssertionResolver
    {
        return new AssertionResolver(
            ValueResolver::createResolver()
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
        if (null !== $examinedValue) {
            $assertion = $assertion->withExaminedValue(
                $this->valueResolver->resolvePageElementReference($examinedValue, $pageProvider)
            );
        }

        $expectedValue = $assertion->getExpectedValue();
        if (null !== $expectedValue) {
            $assertion = $assertion->withExpectedValue(
                $this->valueResolver->resolvePageElementReference($expectedValue, $pageProvider)
            );
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
        if (null !== $examinedValue) {
            $assertion = $assertion->withExaminedValue(
                $this->valueResolver->resolveElementParameter($examinedValue, $identifierCollection)
            );
        }

        $expectedValue = $assertion->getExpectedValue();
        if (null !== $expectedValue) {
            $assertion = $assertion->withExpectedValue(
                $this->valueResolver->resolveElementParameter($expectedValue, $identifierCollection)
            );
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
        if (null !== $examinedValue) {
            $assertion = $assertion->withExaminedValue(
                $this->valueResolver->resolveAttributeParameter($examinedValue, $identifierCollection)
            );
        }

        $expectedValue = $assertion->getExpectedValue();
        if (null !== $expectedValue) {
            $assertion = $assertion->withExpectedValue(
                $this->valueResolver->resolveAttributeParameter($expectedValue, $identifierCollection)
            );
        }

        return $assertion;
    }
}
