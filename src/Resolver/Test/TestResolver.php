<?php

namespace webignition\BasilParser\Resolver\Test;

use webignition\BasilModel\Test\Test;
use webignition\BasilModel\Test\TestInterface;
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
use webignition\BasilParser\Resolver\StepResolver;

class TestResolver
{
    private $configurationResolver;
    private $stepResolver;

    public function __construct(ConfigurationResolver $configurationResolver, StepResolver $stepResolver)
    {
        $this->configurationResolver = $configurationResolver;
        $this->stepResolver = $stepResolver;
    }

    /**
     * @param TestInterface $test
     * @param PageProviderInterface $pageProvider
     * @param StepProviderInterface $stepProvider
     * @param DataSetProviderInterface $dataSetProvider
     *
     * @return TestInterface
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
        TestInterface $test,
        PageProviderInterface $pageProvider,
        StepProviderInterface $stepProvider,
        DataSetProviderInterface $dataSetProvider
    ): TestInterface {
        $configuration = $this->configurationResolver->resolve($test->getConfiguration(), $pageProvider);

        $resolvedSteps = [];
        foreach ($test->getSteps() as $stepName => $step) {
            $resolvedSteps[$stepName] = $this->stepResolver->resolve(
                $step,
                $stepProvider,
                $dataSetProvider,
                $pageProvider
            );
        }

        return new Test($test->getName(), $configuration, $resolvedSteps);
    }
}
