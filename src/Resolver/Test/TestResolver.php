<?php

namespace webignition\BasilParser\Resolver\Test;

use webignition\BasilContextAwareException\ExceptionContext\ExceptionContextInterface;
use webignition\BasilModel\Identifier\IdentifierCollection;
use webignition\BasilModel\Test\Test;
use webignition\BasilModel\Test\TestInterface;
use webignition\BasilModelFactory\InvalidPageElementIdentifierException;
use webignition\BasilModelFactory\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\CircularStepImportException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\NonRetrievableStepException;
use webignition\BasilParser\Exception\UnknownDataProviderException;
use webignition\BasilParser\Exception\UnknownElementException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Exception\UnknownStepException;
use webignition\BasilParser\Provider\DataSet\DataSetProviderInterface;
use webignition\BasilParser\Provider\Page\PageProviderInterface;
use webignition\BasilParser\Provider\Step\StepProviderInterface;
use webignition\BasilParser\Resolver\StepImportResolver;
use webignition\BasilParser\Resolver\StepResolver;

class TestResolver
{
    private $configurationResolver;
    private $stepResolver;
    private $stepImportResolver;

    public function __construct(
        ConfigurationResolver $configurationResolver,
        StepResolver $stepResolver,
        StepImportResolver $stepImportResolver
    ) {
        $this->configurationResolver = $configurationResolver;
        $this->stepResolver = $stepResolver;
        $this->stepImportResolver = $stepImportResolver;
    }

    public static function createResolver(): TestResolver
    {
        return new TestResolver(
            ConfigurationResolver::createResolver(),
            StepResolver::createResolver(),
            StepImportResolver::createResolver()
        );
    }

    /**
     * @param TestInterface $test
     * @param PageProviderInterface $pageProvider
     * @param StepProviderInterface $stepProvider
     * @param DataSetProviderInterface $dataSetProvider
     *
     * @return TestInterface
     *
     * @throws CircularStepImportException
     * @throws InvalidPageElementIdentifierException
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievablePageException
     * @throws NonRetrievableStepException
     * @throws UnknownDataProviderException
     * @throws UnknownElementException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     * @throws UnknownStepException
     */
    public function resolve(
        TestInterface $test,
        PageProviderInterface $pageProvider,
        StepProviderInterface $stepProvider,
        DataSetProviderInterface $dataSetProvider
    ): TestInterface {
        $testName = $test->getName();

        try {
            $configuration = $this->configurationResolver->resolve($test->getConfiguration(), $pageProvider);
        } catch (NonRetrievablePageException | UnknownPageException $contextAwareException) {
            $contextAwareException->applyExceptionContext([
                ExceptionContextInterface::KEY_TEST_NAME => $testName,
            ]);

            throw $contextAwareException;
        }

        $resolvedSteps = [];
        foreach ($test->getSteps() as $stepName => $step) {
            try {
                $resolvedStep = $this->stepImportResolver->resolveStepImport($step, $stepProvider);
                $resolvedStep = $this->stepImportResolver->resolveDataProviderImport($resolvedStep, $dataSetProvider);
                $resolvedStep = $this->stepResolver->resolve($resolvedStep, $pageProvider);
                $resolvedStep = $resolvedStep->withIdentifierCollection(new IdentifierCollection());

                $resolvedSteps[$stepName] = $resolvedStep;
            } catch (InvalidPageElementIdentifierException |
                NonRetrievablePageException |
                NonRetrievableStepException |
                UnknownDataProviderException |
                UnknownElementException |
                UnknownPageElementException |
                UnknownPageException |
                UnknownStepException $contextAwareException
            ) {
                $contextAwareException->applyExceptionContext([
                    ExceptionContextInterface::KEY_TEST_NAME => $testName,
                    ExceptionContextInterface::KEY_STEP_NAME => $stepName,
                ]);

                throw $contextAwareException;
            }
        }

        return new Test($test->getName(), $configuration, $resolvedSteps);
    }
}
