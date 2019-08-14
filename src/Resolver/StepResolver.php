<?php

namespace webignition\BasilParser\Resolver;

use webignition\BasilContextAwareException\ExceptionContext\ExceptionContextInterface;
use webignition\BasilModel\Action\ActionInterface;
use webignition\BasilModel\Assertion\AssertionInterface;
use webignition\BasilModel\Identifier\IdentifierCollection;
use webignition\BasilModel\Step\PendingImportResolutionStepInterface;
use webignition\BasilModel\Step\StepInterface;
use webignition\BasilModelFactory\InvalidPageElementIdentifierException;
use webignition\BasilModelFactory\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\CircularStepImportException;
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
     * @param StepProviderInterface $stepProvider
     * @param DataSetProviderInterface $dataSetProvider
     * @param PageProviderInterface $pageProvider
     * @param array $handledImportNames
     *
     * @return StepInterface
     *
     * @throws CircularStepImportException
     * @throws InvalidPageElementIdentifierException
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
        PageProviderInterface $pageProvider,
        array $handledImportNames = []
    ): StepInterface {
        if ($step instanceof PendingImportResolutionStepInterface && $step->requiresResolution()) {
            $importName = $step->getImportName();
            $dataProviderImportName = $step->getDataProviderImportName();

            if ('' !== $importName) {
                if (in_array($importName, $handledImportNames)) {
                    throw new CircularStepImportException($importName);
                }

                $parentStep = $stepProvider->findStep($importName, $stepProvider, $dataSetProvider, $pageProvider);

                if ($parentStep instanceof PendingImportResolutionStepInterface) {
                    $handledImportNames[] = $importName;

                    $parentStep = $this->resolve(
                        $parentStep,
                        $stepProvider,
                        $dataSetProvider,
                        $pageProvider,
                        $handledImportNames
                    );
                }

                $step = $step
                    ->withPrependedActions($parentStep->getActions())
                    ->withPrependedAssertions($parentStep->getAssertions());
            }

            if ('' !== $dataProviderImportName) {
                $step = $step->withDataSetCollection($dataSetProvider->findDataSetCollection($dataProviderImportName));
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
        } catch (NonRetrievablePageException |
            UnknownPageException |
            UnknownPageElementException $contextAwareException
        ) {
            $exceptionContextContent = null;

            if ($assertion instanceof AssertionInterface) {
                $exceptionContextContent = $assertion->getAssertionString();
            }

            if (null === $exceptionContextContent && $action instanceof ActionInterface) {
                $exceptionContextContent = $action->getActionString();
            }

            $contextAwareException->applyExceptionContext([
                ExceptionContextInterface::KEY_CONTENT => $exceptionContextContent,
            ]);

            throw $contextAwareException;
        }

        $step = $step->withActions($resolvedActions);
        $step = $step->withAssertions($resolvedAssertions);

        $resolvedElementIdentifiers = [];
        foreach ($step->getIdentifierCollection() as $identifier) {
            $resolvedElementIdentifiers[] = $this->identifierResolver->resolve($identifier, $pageProvider);
        }

        $step = $step->withIdentifierCollection(new IdentifierCollection($resolvedElementIdentifiers));

        return $step;
    }
}
