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
        if (null !== $examinedValue) {
            $resolvedValue = $this->valueResolver->resolve($examinedValue, $pageProvider, $identifierCollection);

            $assertion = $assertion->withExaminedValue($resolvedValue);
        }

        $expectedValue = $assertion->getExpectedValue();
        if (null !== $expectedValue) {
            $resolvedValue = $this->valueResolver->resolve($expectedValue, $pageProvider, $identifierCollection);

            $assertion = $assertion->withExpectedValue($resolvedValue);
        }

        return $assertion;
    }
}
