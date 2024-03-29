<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Resolver;

use webignition\BasilModels\Model\Action\ActionInterface;
use webignition\BasilModels\Model\Assertion\AssertionInterface;
use webignition\BasilModels\Model\Step\StepInterface;
use webignition\BasilModels\Provider\Exception\UnknownItemException;
use webignition\BasilModels\Provider\Identifier\EmptyIdentifierProvider;
use webignition\BasilModels\Provider\Identifier\IdentifierProvider;
use webignition\BasilModels\Provider\Page\PageProviderInterface;

class StepResolver
{
    public function __construct(
        private readonly StatementResolver $statementResolver,
        private readonly ElementResolver $elementResolver
    ) {
    }

    public static function createResolver(): StepResolver
    {
        return new StepResolver(
            StatementResolver::createResolver(),
            ElementResolver::createResolver()
        );
    }

    /**
     * @throws UnknownElementException
     * @throws UnknownPageElementException
     * @throws UnknownItemException
     */
    public function resolve(StepInterface $step, PageProviderInterface $pageProvider): StepInterface
    {
        if ($step->requiresImportResolution()) {
            return $step;
        }

        $step = $this->resolveIdentifiers($step, $pageProvider);
        $step = $this->resolveActions($step, $pageProvider);

        return $this->resolveAssertions($step, $pageProvider);
    }

    /**
     * @throws UnknownElementException
     * @throws UnknownPageElementException
     * @throws UnknownItemException
     */
    private function resolveIdentifiers(StepInterface $step, PageProviderInterface $pageProvider): StepInterface
    {
        $resolvedIdentifiers = [];
        $identifierProvider = new EmptyIdentifierProvider();

        foreach ($step->getIdentifiers() as $name => $identifier) {
            $resolvedIdentifiers[$name] = $this->elementResolver->resolve(
                $identifier,
                $pageProvider,
                $identifierProvider
            );
        }

        return $step->withIdentifiers($resolvedIdentifiers);
    }

    /**
     * @throws UnknownElementException
     * @throws UnknownPageElementException
     * @throws UnknownItemException
     */
    private function resolveActions(StepInterface $step, PageProviderInterface $pageProvider): StepInterface
    {
        $resolvedActions = [];
        $identifierProvider = new IdentifierProvider($step->getIdentifiers());
        $action = null;

        try {
            foreach ($step->getActions() as $action) {
                $resolvedActions[] = $this->statementResolver->resolve($action, $pageProvider, $identifierProvider);
            }
        } catch (UnknownElementException | UnknownPageElementException | UnknownItemException $exception) {
            if ($action instanceof ActionInterface) {
                $exception->setContent($action->getSource());
            }

            throw $exception;
        }

        $resolvedActions = array_filter($resolvedActions, function ($item) {
            return $item instanceof ActionInterface;
        });

        return $step->withActions($resolvedActions);
    }

    /**
     * @throws UnknownElementException
     * @throws UnknownPageElementException
     * @throws UnknownItemException
     */
    private function resolveAssertions(StepInterface $step, PageProviderInterface $pageProvider): StepInterface
    {
        $resolvedAssertions = [];
        $identifierProvider = new IdentifierProvider($step->getIdentifiers());
        $assertion = null;

        try {
            foreach ($step->getAssertions() as $assertion) {
                $resolvedAssertions[] = $this->statementResolver->resolve(
                    $assertion,
                    $pageProvider,
                    $identifierProvider
                );
            }
        } catch (UnknownElementException | UnknownPageElementException | UnknownItemException $exception) {
            if ($assertion instanceof AssertionInterface) {
                $exception->setContent($assertion->getSource());
            }

            throw $exception;
        }

        $resolvedAssertions = array_filter($resolvedAssertions, function ($item) {
            return $item instanceof AssertionInterface;
        });

        return $step->withAssertions($resolvedAssertions);
    }
}
