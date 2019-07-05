<?php

namespace webignition\BasilParser\Resolver;

use webignition\BasilModel\Assertion\AssertionInterface;
use webignition\BasilModel\IdentifierContainerInterface;
use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Provider\Page\PageProviderInterface;

class AssertionResolver
{
    /**
     * @var PageModelElementIdentifierResolver
     */
    private $pageModelElementIdentifierResolver;

    public function __construct(PageModelElementIdentifierResolver $pageModelElementIdentifierResolver)
    {
        $this->pageModelElementIdentifierResolver = $pageModelElementIdentifierResolver;
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
        if ($assertion instanceof IdentifierContainerInterface) {
            $resolvedAssertion = $this->pageModelElementIdentifierResolver->resolve($assertion, $pageProvider);

            if ($resolvedAssertion instanceof AssertionInterface) {
                return $resolvedAssertion;
            }
        }

        return $assertion;
    }
}
