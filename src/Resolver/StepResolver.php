<?php

namespace webignition\BasilParser\Resolver;

use webignition\BasilModel\ExceptionContext\ExceptionContextInterface;
use webignition\BasilModel\Step\PendingImportResolutionStepInterface;
use webignition\BasilModel\Step\StepInterface;
use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievableDataProviderException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\NonRetrievableStepException;
use webignition\BasilParser\Exception\UnknownDataProviderException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Exception\UnknownStepException;
use webignition\BasilParser\Provider\DataSet\DataSetProviderInterface;
use webignition\BasilParser\Provider\Page\PageProviderInterface;
use webignition\BasilParser\Provider\Step\StepProviderInterface;

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

    /**
     * @param StepInterface $step
     * @param StepProviderInterface $stepProvider
     * @param DataSetProviderInterface $dataSetProvider
     * @param PageProviderInterface $pageProvider
     *
     * @return StepInterface
     *
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievableDataProviderException
     * @throws NonRetrievablePageException
     * @throws NonRetrievableStepException
     * @throws UnknownDataProviderException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     * @throws UnknownStepException
     */
    public function resolve(
        StepInterface $step,
        StepProviderInterface $stepProvider,
        DataSetProviderInterface $dataSetProvider,
        PageProviderInterface $pageProvider
    ): StepInterface {
        if ($step instanceof PendingImportResolutionStepInterface && $step->requiresResolution()) {
            $importName = $step->getImportName();
            $dataProviderImportName = $step->getDataProviderImportName();

            if ('' !== $importName) {
                $parentStep = $stepProvider->findStep($importName, $stepProvider, $dataSetProvider, $pageProvider);

                if ($parentStep instanceof PendingImportResolutionStepInterface) {
                    $parentStep = $this->resolve($parentStep, $stepProvider, $dataSetProvider, $pageProvider);
                }

                $step = $step
                    ->withPrependedActions($parentStep->getActions())
                    ->withPrependedAssertions($parentStep->getAssertions());
            }

            if ('' !== $dataProviderImportName) {
                $step = $step->withDataSets($dataSetProvider->findDataSetCollection($dataProviderImportName));
            }

            if ($step instanceof PendingImportResolutionStepInterface) {
                $step = $step->getStep();
            }
        }

        $resolvedActions = [];
        $resolvedAssertions = [];

        $action = null;
        $assertion = null;

        try {
            foreach ($step->getActions() as $action) {
                $resolvedActions[] = $this->actionResolver->resolve($action, $pageProvider);
            }

            foreach ($step->getAssertions() as $assertion) {
                $resolvedAssertions[] = $this->assertionResolver->resolve($assertion, $pageProvider);
            }
        } catch (NonRetrievablePageException | UnknownPageException $contextAwareException) {
            $exceptionContextContent = null === $action
                ? $assertion->getAssertionString()
                : $action->getActionString();

            $contextAwareException->applyExceptionContext([
                ExceptionContextInterface::KEY_CONTENT => $exceptionContextContent,
            ]);

            throw $contextAwareException;
        }

        $step = $step->withActions($resolvedActions);
        $step = $step->withAssertions($resolvedAssertions);

        $resolvedElementIdentifiers = [];
        foreach ($step->getElementIdentifiers() as $elementName => $elementIdentifier) {
            $resolvedElementIdentifiers[$elementName] = $this->identifierResolver->resolve(
                $elementIdentifier,
                $pageProvider
            );
        }

        $step = $step->withElementIdentifiers($resolvedElementIdentifiers);

        return $step;
    }
}
