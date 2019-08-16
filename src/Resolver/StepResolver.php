<?php

namespace webignition\BasilParser\Resolver;

use webignition\BasilContextAwareException\ExceptionContext\ExceptionContextInterface;
use webignition\BasilModel\Action\ActionInterface;
use webignition\BasilModel\Assertion\AssertionInterface;
use webignition\BasilModel\Identifier\IdentifierCollection;
use webignition\BasilModel\Step\PendingImportResolutionStep;
use webignition\BasilModel\Step\StepInterface;
use webignition\BasilModelFactory\InvalidPageElementIdentifierException;
use webignition\BasilModelFactory\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownElementException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Provider\Page\PageProviderInterface;

class StepResolver
{
    private $actionResolver;
    private $assertionResolver;
    private $identifierResolver;

    public function __construct(
        ActionResolver $actionResolver,
        AssertionResolver $assertionResolver,
        IdentifierResolver $identifierResolver
    ) {
        $this->actionResolver = $actionResolver;
        $this->assertionResolver = $assertionResolver;
        $this->identifierResolver = $identifierResolver;
    }

    public static function createResolver(): StepResolver
    {
        return new StepResolver(
            ActionResolver::createResolver(),
            AssertionResolver::createResolver(),
            IdentifierResolver::createResolver()
        );
    }

    /**
     * @param StepInterface $step
     * @param PageProviderInterface $pageProvider
     *
     * @return StepInterface
     *
     * @throws InvalidPageElementIdentifierException
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievablePageException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     */
    public function resolvePageElementReferences(
        StepInterface $step,
        PageProviderInterface $pageProvider
    ): StepInterface {
        if ($step instanceof PendingImportResolutionStep) {
            if ($step->requiresResolution()) {
                return $step;
            }

            $step = $step->getStep();
        }

        $step = $this->resolveIdentifierCollectionPageElementReferences($step, $pageProvider);
        $step = $this->resolveActionPageElementReferences($step, $pageProvider);
        $step = $this->resolveAssertionPageElementReferences($step, $pageProvider);

        return $step;
    }

    /**
     * @param StepInterface $step
     *
     * @return StepInterface
     *
     * @throws UnknownElementException
     */
    public function resolveElementAndAttributeParameters(
        StepInterface $step
    ): StepInterface {
        if ($step instanceof PendingImportResolutionStep) {
            if ($step->requiresResolution()) {
                return $step;
            }

            $step = $step->getStep();
        }

        $step = $this->resolveIdentifierCollectionElementParameters($step);
        $step = $this->resolveActionElementAndAttributeParameters($step);
        $step = $this->resolveAssertionElementAndAttributeParameters($step);

        return $step;
    }

    /**
     * @param StepInterface $step
     * @param PageProviderInterface $pageProvider
     *
     * @return StepInterface
     *
     * @throws InvalidPageElementIdentifierException
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievablePageException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     */
    private function resolveIdentifierCollectionPageElementReferences(
        StepInterface $step,
        PageProviderInterface $pageProvider
    ): StepInterface {
        $resolvedIdentifiers = [];
        foreach ($step->getIdentifierCollection() as $identifier) {
            $resolvedIdentifiers[] = $this->identifierResolver->resolvePageElementReference($identifier, $pageProvider);
        }

        return $step->withIdentifierCollection(new IdentifierCollection($resolvedIdentifiers));
    }

    /**
     * @param StepInterface $step
     *
     * @return StepInterface
     *
     * @throws UnknownElementException
     */
    private function resolveIdentifierCollectionElementParameters(StepInterface $step): StepInterface
    {
        $resolvedIdentifiers = [];
        foreach ($step->getIdentifierCollection() as $identifier) {
            $resolvedIdentifiers[] = $this->identifierResolver->resolveElementParameter(
                $identifier,
                new IdentifierCollection()
            );
        }

        return $step->withIdentifierCollection(new IdentifierCollection($resolvedIdentifiers));
    }

    /**
     * @param StepInterface $step
     * @param PageProviderInterface $pageProvider
     *
     * @return StepInterface
     *
     * @throws InvalidPageElementIdentifierException
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievablePageException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     */
    private function resolveActionPageElementReferences(
        StepInterface $step,
        PageProviderInterface $pageProvider
    ): StepInterface {
        $resolvedActions = [];
        $action = null;

        try {
            foreach ($step->getActions() as $action) {
                $resolvedActions[] = $this->actionResolver->resolvePageElementReferences(
                    $action,
                    $pageProvider
                );
            }
        } catch (InvalidPageElementIdentifierException |
            NonRetrievablePageException |
            UnknownPageElementException |
            UnknownPageException $contextAwareException
        ) {
            if ($action instanceof ActionInterface) {
                $contextAwareException->applyExceptionContext([
                    ExceptionContextInterface::KEY_CONTENT => $action->getActionString(),
                ]);
            }

            throw $contextAwareException;
        }

        return $step->withActions($resolvedActions);
    }

    /**
     * @param StepInterface $step
     *
     * @return StepInterface
     *
     * @throws UnknownElementException
     */
    private function resolveActionElementAndAttributeParameters(StepInterface $step): StepInterface
    {
        $resolvedActions = [];
        $action = null;

        $identifierCollection = $step->getIdentifierCollection();

        try {
            foreach ($step->getActions() as $action) {
                $resolvedAction = $this->actionResolver->resolveElementParameters(
                    $action,
                    $identifierCollection
                );

                $resolvedAction = $this->actionResolver->resolveAttributeParameters(
                    $resolvedAction,
                    $identifierCollection
                );

                $resolvedActions[] = $resolvedAction;
            }
        } catch (UnknownElementException $contextAwareException) {
            if ($action instanceof ActionInterface) {
                $contextAwareException->applyExceptionContext([
                    ExceptionContextInterface::KEY_CONTENT => $action->getActionString(),
                ]);
            }

            throw $contextAwareException;
        }

        return $step->withActions($resolvedActions);
    }

    /**
     * @param StepInterface $step
     * @param PageProviderInterface $pageProvider
     *
     * @return StepInterface
     *
     * @throws InvalidPageElementIdentifierException
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievablePageException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     */
    private function resolveAssertionPageElementReferences(
        StepInterface $step,
        PageProviderInterface $pageProvider
    ): StepInterface {
        $resolvedAssertions = [];
        $assertion = null;

        try {
            foreach ($step->getAssertions() as $assertion) {
                $resolvedAssertions[] = $this->assertionResolver->resolvePageElementReferences(
                    $assertion,
                    $pageProvider
                );
            }
        } catch (InvalidPageElementIdentifierException |
            NonRetrievablePageException |
            UnknownPageElementException |
            UnknownPageException $contextAwareException
        ) {
            $exceptionContextContent = null;

            if ($assertion instanceof AssertionInterface) {
                $contextAwareException->applyExceptionContext([
                    ExceptionContextInterface::KEY_CONTENT => $assertion->getAssertionString(),
                ]);
            }

            throw $contextAwareException;
        }

        return $step->withAssertions($resolvedAssertions);
    }

    /**
     * @param StepInterface $step
     *
     * @return StepInterface
     *
     * @throws UnknownElementException
     */
    private function resolveAssertionElementAndAttributeParameters(StepInterface $step): StepInterface
    {
        $resolvedAssertions = [];
        $assertion = null;

        $identifierCollection = $step->getIdentifierCollection();

        try {
            foreach ($step->getAssertions() as $assertion) {
                $resolvedAssertion = $this->assertionResolver->resolveElementParameters(
                    $assertion,
                    $identifierCollection
                );

                $resolvedAssertion = $this->assertionResolver->resolveAttributeParameters(
                    $resolvedAssertion,
                    $identifierCollection
                );

                $resolvedAssertions[] = $resolvedAssertion;
            }
        } catch (UnknownElementException $contextAwareException) {
            $exceptionContextContent = null;

            if ($assertion instanceof AssertionInterface) {
                $contextAwareException->applyExceptionContext([
                    ExceptionContextInterface::KEY_CONTENT => $assertion->getAssertionString(),
                ]);
            }

            throw $contextAwareException;
        }

        return $step->withAssertions($resolvedAssertions);
    }
}
