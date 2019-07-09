<?php

namespace webignition\BasilParser\Resolver;

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
                $parentStep = $stepProvider->findStep($importName);

                $step = $step
                    ->withPrependedActions($parentStep->getActions())
                    ->withPrependedAssertions($parentStep->getAssertions());
            }

            if ('' !== $dataProviderImportName) {
                $step = $step->withDataSets(
                    $dataSetProvider->findDataSetCollection($step->getDataProviderImportName())
                );
            }

            $step = $step->getStep();
        }

        $resolvedActions = [];
        foreach ($step->getActions() as $action) {
            $resolvedActions[] = $this->actionResolver->resolve($action, $pageProvider);
        }

        $step = $step->withActions($resolvedActions);

        $resolvedAssertions = [];
        foreach ($step->getAssertions() as $assertion) {
            $resolvedAssertions[] = $this->assertionResolver->resolve($assertion, $pageProvider);
        }

        $step = $step->withAssertions($resolvedAssertions);

        $resolvedElementIdentifiers = [];
        foreach ($step->getElementIdentifiers() as $elementIdentifier) {
            $resolvedElementIdentifiers[] = $this->identifierResolver->resolve($elementIdentifier, $pageProvider);
        }

        $step = $step->withElementIdentifiers($resolvedElementIdentifiers);

        return $step;
    }
}