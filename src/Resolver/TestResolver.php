<?php

declare(strict_types=1);

namespace webignition\BasilLoader\Resolver;

use webignition\BasilContextAwareException\ExceptionContext\ExceptionContextInterface;
use webignition\BasilModels\Model\Step\StepCollection;
use webignition\BasilModels\Model\Step\StepInterface;
use webignition\BasilModels\Model\Test\Test;
use webignition\BasilModels\Model\Test\TestInterface;
use webignition\BasilModels\Provider\DataSet\DataSetProviderInterface;
use webignition\BasilModels\Provider\Exception\UnknownItemException;
use webignition\BasilModels\Provider\Page\PageProviderInterface;
use webignition\BasilModels\Provider\Step\StepProviderInterface;

class TestResolver
{
    public function __construct(
        private TestConfigurationResolver $configurationResolver,
        private StepResolver $stepResolver,
        private StepImportResolver $stepImportResolver
    ) {
    }

    public static function createResolver(): TestResolver
    {
        return new TestResolver(
            TestConfigurationResolver::createResolver(),
            StepResolver::createResolver(),
            StepImportResolver::createResolver()
        );
    }

    /**
     * @throws CircularStepImportException
     * @throws UnknownElementException
     * @throws UnknownItemException
     * @throws UnknownPageElementException
     */
    public function resolve(
        TestInterface $test,
        PageProviderInterface $pageProvider,
        StepProviderInterface $stepProvider,
        DataSetProviderInterface $dataSetProvider
    ): TestInterface {
        $configuration = $this->configurationResolver->resolve($test->getConfiguration(), $pageProvider);

        $resolvedSteps = [];
        foreach ($test->getSteps() as $stepName => $step) {
            if ($step instanceof StepInterface) {
                try {
                    $resolvedStep = $this->stepImportResolver->resolveStepImport($step, $stepProvider);
                    $resolvedStep = $this->stepImportResolver->resolveDataProviderImport(
                        $resolvedStep,
                        $dataSetProvider
                    );
                    $resolvedStep = $this->stepResolver->resolve($resolvedStep, $pageProvider);
                    $resolvedStep = $resolvedStep->withIdentifiers([]);

                    $resolvedSteps[$stepName] = $resolvedStep;
                } catch (
                    UnknownElementException |
                    UnknownItemException |
                    UnknownPageElementException $contextAwareException
                ) {
                    $contextAwareException->applyExceptionContext([
                        ExceptionContextInterface::KEY_STEP_NAME => (string) $stepName,
                    ]);

                    throw $contextAwareException;
                }
            }
        }

        return new Test($configuration, new StepCollection($resolvedSteps));
    }
}
