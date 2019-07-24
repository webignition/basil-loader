<?php

namespace webignition\BasilParser\Resolver;

use webignition\BasilModel\Assertion\AssertionInterface;
use webignition\BasilModel\Value\ElementValue;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModelFactory\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
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
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievablePageException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     */
    public function resolve(AssertionInterface $assertion, PageProviderInterface $pageProvider): AssertionInterface
    {
        $examinedValue = $assertion->getExaminedValue();

        if (!$examinedValue instanceof ObjectValue) {
            return $assertion;
        }

        $resolvedIdentifier = $this->pageElementReferenceResolver->resolve($examinedValue, $pageProvider);

        return $assertion->withExaminedValue(new ElementValue($resolvedIdentifier));
    }
}
